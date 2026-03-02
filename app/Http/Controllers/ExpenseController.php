<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseShare;
use App\Models\Apartment;
use App\Models\NotificationLog;
use App\Models\SubscriptionType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $expenses = Expense::with('creator')->select('expenses.*');

            return DataTables::of($expenses)
                ->addColumn('creator_name', function ($expense) {
                    return $expense->creator ? $expense->creator->name : '-';
                })
                ->addColumn('formatted_date', function ($expense) {
                    return $expense->date ? $expense->date->format('Y-m-d') : '-';
                })
                ->addColumn('formatted_amount', function ($expense) {
                    return number_format($expense->amount, 2) . ' ج.م';
                })
                ->addColumn('distribution_label', function ($expense) {
                    return $expense->distribution_type === 'all'
                        ? '<span class="badge bg-primary">جميع السكان</span>'
                        : '<span class="badge bg-warning">شقة محددة</span>';
                })
                ->addColumn('action', function ($expense) {
                    $buttons = '
                        <button class="btn btn-sm btn-primary view-shares-btn" data-id="' . $expense->id . '">عرض الحصص</button>
                        <button class="btn btn-sm btn-info edit-btn" data-id="' . $expense->id . '">تعديل</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="' . $expense->id . '">حذف</button>
                    ';
                    return $buttons;
                })
                ->rawColumns(['distribution_label', 'action'])
                ->make(true);
        }

        return view('expenses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apartments = Apartment::all();
        $subscriptionTypes = SubscriptionType::where('tenant_id', auth()->user()->tenant_id)->get();
        return response()->json(['html' => view('expenses.create', compact('apartments', 'subscriptionTypes'))->render()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'distribution_type' => 'required|in:all,specific',
            'apartment_id' => 'required_if:distribution_type,specific|nullable|exists:apartments,id',
            'recurrence_type' => 'required|in:one_time,monthly,yearly',
            'subscription_type_id' => 'nullable|exists:subscription_types,id',
            'next_occurrence_date' => 'nullable|date',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['created_by'] = Auth::id();
        $validated['status'] = 'pending';
        $validated['is_recurring'] = $validated['recurrence_type'] !== 'one_time';

        // إذا تم اختيار subscription type، استخدم مبلغه وعنوانه
        if (!empty($validated['subscription_type_id'])) {
            $subscriptionType = SubscriptionType::find($validated['subscription_type_id']);
            if ($subscriptionType) {
                $validated['amount'] = $subscriptionType->amount;
                if (empty($validated['title'])) {
                    $validated['title'] = $subscriptionType->name;
                }
            }
        }

        DB::beginTransaction();
        try {
            $expense = Expense::create($validated);

            // توزيع المصروف تلقائياً باستخدام method من Expense Model
            $distributed = $expense->distribute();

            if ($distributed) {
                // إرسال إشعارات للسكان
                foreach ($expense->shares as $share) {
                    if ($share->apartment->resident) {
                        $notificationMessage = 'تم توزيع مصروف جديد. حصتك: ' . number_format($share->share_amount, 2) . ' ج.م';

                        if ($expense->is_recurring) {
                            $recurrenceLabels = [
                                'monthly' => 'شهري',
                                'yearly' => 'سنوي'
                            ];
                            $recurrenceLabel = $recurrenceLabels[$expense->recurrence_type] ?? '';
                            $notificationMessage .= ' (مصروف ' . $recurrenceLabel . ' متكرر)';
                        }

                        NotificationLog::create([
                            'tenant_id' => auth()->user()->tenant_id,
                            'user_id' => $share->apartment->resident->id,
                            'notification_type' => 'building_expense',
                            'related_type' => 'Expense',
                            'related_id' => $expense->id,
                            'title' => 'مصروف جديد: ' . $expense->title,
                            'message' => $notificationMessage,
                            'sent_at' => now(),
                            'sent_by' => auth()->id(),
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم إضافة المصروف وتوزيعه بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء إضافة المصروف: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $apartments = Apartment::all();
        $subscriptionTypes = SubscriptionType::where('tenant_id', auth()->user()->tenant_id)->get();
        return response()->json(['html' => view('expenses.edit', compact('expense', 'apartments', 'subscriptionTypes'))->render()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'distribution_type' => 'required|in:all,specific',
            'apartment_id' => 'required_if:distribution_type,specific|nullable|exists:apartments,id',
            'recurrence_type' => 'required|in:one_time,monthly,yearly',
            'subscription_type_id' => 'nullable|exists:subscription_types,id',
            'next_occurrence_date' => 'nullable|date',
        ]);

        $validated['is_recurring'] = $validated['recurrence_type'] !== 'one_time';

        // إذا تم اختيار subscription type، استخدم مبلغه
        if (!empty($validated['subscription_type_id'])) {
            $subscriptionType = SubscriptionType::find($validated['subscription_type_id']);
            if ($subscriptionType) {
                $validated['amount'] = $subscriptionType->amount;
            }
        }

        DB::beginTransaction();
        try {
            $expense->update($validated);

            // إعادة توزيع المصروف إذا تغيرت التفاصيل
            if ($expense->status === 'distributed') {
                // حذف التوزيع القديم
                $expense->shares()->delete();

                // توزيع جديد
                $expense->update(['status' => 'pending']);
                $distributed = $expense->distribute();

                if ($distributed) {
                    // إرسال إشعارات للسكان
                    foreach ($expense->shares as $share) {
                        if ($share->apartment->resident) {
                            NotificationLog::create([
                                'tenant_id' => auth()->user()->tenant_id,
                                'user_id' => $share->apartment->resident->id,
                                'notification_type' => 'building_expense',
                                'related_type' => 'Expense',
                                'related_id' => $expense->id,
                                'title' => 'تحديث مصروف: ' . $expense->title,
                                'message' => 'تم تحديث مصروف. حصتك الجديدة: ' . number_format($share->share_amount, 2) . ' ج.م',
                                'sent_at' => now(),
                                'sent_by' => auth()->id(),
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم تعديل المصروف بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تعديل المصروف: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف المصروف بنجاح']);
    }

    /**
     * Mark expense share as paid
     */
    public function markSharePaid($shareId)
    {
        $share = ExpenseShare::findOrFail($shareId);
        $share->update([
            'paid' => true,
            'paid_at' => now(),
        ]);

        // Notify the resident
        $apartment = $share->apartment;
        if ($apartment && $apartment->resident) {
            NotificationController::notifyUser(
                $apartment->resident->id,
                'payment',
                $share->expense_id,
                'تم تسجيل الدفع',
                'تم تسجيل دفع حصتك من المصروف: ' . $share->expense->title
            );
        }

        return response()->json(['success' => true, 'message' => 'تم تسجيل الدفع بنجاح']);
    }

    /**
     * Show expense shares page
     */
    public function shares(Request $request, $expenseId)
    {
        $expense = Expense::with('shares.apartment.resident')->findOrFail($expenseId);

        if ($request->ajax()) {
            $shares = $expense->shares()->with('apartment.resident')->get();

            return DataTables::of($shares)
                ->addColumn('apartment_number', function ($share) {
                    return $share->apartment ? $share->apartment->number : '-';
                })
                ->addColumn('resident_name', function ($share) {
                    return $share->apartment && $share->apartment->resident ? $share->apartment->resident->name : '-';
                })
                ->addColumn('formatted_amount', function ($share) {
                    return number_format($share->share_amount, 2) . ' ج.م';
                })
                ->addColumn('paid_label', function ($share) {
                    return $share->paid
                        ? '<span class="badge bg-success">مدفوع</span>'
                        : '<span class="badge bg-danger">غير مدفوع</span>';
                })
                ->addColumn('action', function ($share) {
                    if (!$share->paid) {
                        return '<button class="btn btn-sm btn-success mark-paid-btn" data-id="' . $share->id . '">تسجيل الدفع</button>';
                    }
                    return '-';
                })
                ->rawColumns(['paid_label', 'action'])
                ->make(true);
        }

        return view('expenses.shares', compact('expense'));
    }
}
