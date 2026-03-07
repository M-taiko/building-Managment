<?php

namespace App\Http\Controllers;

use App\Models\MonthlyDue;
use App\Models\Apartment;
use App\Models\BuildingFundTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MonthlyDueController extends Controller
{
    /**
     * عرض صفحة إعدادات المستحقات الشهرية (للأدمن فقط)
     */
    public function settings()
    {
        $tenant = auth()->user()->tenant;
        $apartments = Apartment::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        return view('monthly-dues.settings', compact('apartments'));
    }

    /**
     * تحديد/تحديث المبلغ الشهري لجميع الشقق
     */
    public function updateMonthlyAmount(Request $request)
    {
        $validated = $request->validate([
            'monthly_amount' => 'required|numeric|min:0',
            'apply_to' => 'required|in:all,existing',
        ]);

        $tenant = auth()->user()->tenant;
        $year = now()->year;
        $month = now()->month;

        DB::beginTransaction();
        try {
            $apartments = Apartment::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->get();

            foreach ($apartments as $apartment) {
                // التحقق من عدم وجود مستحق شهري بالفعل
                $existingDue = MonthlyDue::where('apartment_id', $apartment->id)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->first();

                if (!$existingDue) {
                    MonthlyDue::create([
                        'tenant_id' => $tenant->id,
                        'apartment_id' => $apartment->id,
                        'year' => $year,
                        'month' => $month,
                        'amount' => $validated['monthly_amount'],
                        'paid_amount' => 0,
                        'status' => 'unpaid',
                    ]);
                } elseif ($validated['apply_to'] === 'all' && $existingDue->status !== 'paid') {
                    // تحديث المبلغ للمستحقات الموجودة وغير المدفوعة
                    $existingDue->update(['amount' => $validated['monthly_amount']]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'تم تحديد المبلغ الشهري لجميع الشقق بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض المستحقات الشهرية للداشبورد
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        // للساكن: عرض مستحقاته فقط
        if ($user->role === 'resident') {
            $dues = MonthlyDue::with('apartment')
                ->where('apartment_id', $user->apartment_id)
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return view('monthly-dues.resident', compact('dues'));
        }

        // للأدمن: عرض كل المستحقات
        if ($request->ajax()) {
            $query = MonthlyDue::with('apartment')
                ->where('tenant_id', $tenant->id);

            // الفلاتر
            if ($request->has('year') && $request->year != '') {
                $query->where('year', $request->year);
            }
            if ($request->has('month') && $request->month != '') {
                $query->where('month', $request->month);
            }
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            $dues = $query->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            return DataTables::of($dues)
                ->addColumn('apartment_number', function ($due) {
                    return $due->apartment->number;
                })
                ->addColumn('resident_name', function ($due) {
                    return $due->apartment->resident ? $due->apartment->resident->name : '-';
                })
                ->addColumn('period', function ($due) {
                    return $due->year . '-' . str_pad($due->month, 2, '0', STR_PAD_LEFT);
                })
                ->addColumn('amount_formatted', function ($due) {
                    return number_format($due->amount, 2) . ' ج.م';
                })
                ->addColumn('paid_amount_formatted', function ($due) {
                    return number_format($due->paid_amount, 2) . ' ج.م';
                })
                ->addColumn('remaining_formatted', function ($due) {
                    return number_format($due->remaining_amount, 2) . ' ج.م';
                })
                ->addColumn('status_badge', function ($due) {
                    $badges = [
                        'paid' => '<span class="badge bg-success">مدفوع</span>',
                        'partial' => '<span class="badge bg-warning">مدفوع جزئياً</span>',
                        'unpaid' => '<span class="badge bg-danger">غير مدفوع</span>',
                    ];
                    return $badges[$due->status] ?? '-';
                })
                ->addColumn('action', function ($due) {
                    if ($due->status !== 'paid') {
                        return '<button class="btn btn-sm btn-success pay-btn" data-id="' . $due->id . '">تسديد</button>';
                    }
                    return '-';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('monthly-dues.index');
    }

    /**
     * تسديد المستحق الشهري
     */
    public function pay(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $due = MonthlyDue::findOrFail($id);

            // التحقق من أن المبلغ لا يتجاوز المتبقي
            if ($validated['amount'] > $due->remaining_amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'المبلغ المدخل أكبر من المتبقي'
                ], 422);
            }

            // تحديث المستحق الشهري
            $due->paid_amount += $validated['amount'];

            if ($due->paid_amount >= $due->amount) {
                $due->status = 'paid';
                $due->paid_at = now();
            } elseif ($due->paid_amount > 0) {
                $due->status = 'partial';
            }

            $due->save();

            // إضافة المبلغ إلى حساب العمارة
            BuildingFundTransaction::addIncome(
                $due->tenant_id,
                $validated['amount'],
                'monthly_due',
                $due->id,
                'تسديد المستحق الشهري - شقة ' . $due->apartment->number . ' - ' . $due->year . '/' . $due->month
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'تم التسديد بنجاح وإضافة المبلغ إلى حساب العمارة'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * توليد المستحقات الشهرية لشهر جديد
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric|min:0',
        ]);

        $tenant = auth()->user()->tenant;

        DB::beginTransaction();
        try {
            $apartments = Apartment::where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->get();

            $created = 0;
            foreach ($apartments as $apartment) {
                $existingDue = MonthlyDue::where('apartment_id', $apartment->id)
                    ->where('year', $validated['year'])
                    ->where('month', $validated['month'])
                    ->first();

                if (!$existingDue) {
                    MonthlyDue::create([
                        'tenant_id' => $tenant->id,
                        'apartment_id' => $apartment->id,
                        'year' => $validated['year'],
                        'month' => $validated['month'],
                        'amount' => $validated['amount'],
                        'paid_amount' => 0,
                        'status' => 'unpaid',
                    ]);
                    $created++;
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "تم توليد {$created} مستحق شهري بنجاح"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء مطالب شهرية متعددة (شهور × وحدات)
     */
    public function bulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'months' => 'required|array|min:1',
            'months.*' => 'integer|between:1,12',
            'apartment_ids' => 'required|array|min:1',
            'apartment_ids.*' => 'integer|exists:apartments,id',
        ]);

        $tenant = auth()->user()->tenant;
        $year = now()->year;
        $created = 0;

        DB::beginTransaction();
        try {
            // Loop through each combination of month and apartment
            foreach ($validated['months'] as $month) {
                foreach ($validated['apartment_ids'] as $apartmentId) {
                    // Check if it already exists
                    $exists = MonthlyDue::where('apartment_id', $apartmentId)
                        ->where('year', $year)
                        ->where('month', $month)
                        ->where('type', $validated['type'])
                        ->exists();

                    if (!$exists) {
                        MonthlyDue::create([
                            'tenant_id' => $tenant->id,
                            'apartment_id' => $apartmentId,
                            'year' => $year,
                            'month' => $month,
                            'type' => $validated['type'],
                            'amount' => $validated['amount'],
                            'status' => 'unpaid',
                            'created_by' => auth()->id(),
                        ]);
                        $created++;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "تم إنشاء $created مطلب شهري بنجاح",
                'count' => $created,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
