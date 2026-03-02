<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionType;
use App\Models\Apartment;
use App\Models\BuildingFundTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $subscriptions = Subscription::with(['apartment', 'subscriptionType'])->select('subscriptions.*');

            return DataTables::of($subscriptions)
                ->addColumn('apartment_number', function ($subscription) {
                    return $subscription->apartment ? $subscription->apartment->number : '-';
                })
                ->addColumn('subscription_type_name', function ($subscription) {
                    return $subscription->subscriptionType ? $subscription->subscriptionType->name : '-';
                })
                ->addColumn('status_label', function ($subscription) {
                    $statusLabels = [
                        'pending' => '<span class="badge bg-warning">معلق</span>',
                        'paid' => '<span class="badge bg-success">مدفوع</span>',
                        'partial' => '<span class="badge bg-info">مدفوع جزئياً</span>',
                        'overdue' => '<span class="badge bg-danger">متأخر</span>',
                    ];
                    return $statusLabels[$subscription->status] ?? '<span class="badge bg-secondary">غير محدد</span>';
                })
                ->addColumn('action', function ($subscription) {
                    $buttons = '';
                    if ($subscription->status !== 'paid') {
                        $buttons .= '<button class="btn btn-sm btn-success mark-paid-btn" data-id="' . $subscription->id . '">تسجيل الدفع</button> ';
                    }
                    $buttons .= '
                        <button class="btn btn-sm btn-info edit-btn" data-id="' . $subscription->id . '">تعديل</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="' . $subscription->id . '">حذف</button>
                    ';
                    return $buttons;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        }

        return view('subscriptions.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $apartments = Apartment::all();
        $subscriptionTypes = SubscriptionType::where('is_active', true)->get();
        return response()->json(['html' => view('subscriptions.create', compact('apartments', 'subscriptionTypes'))->render()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'subscription_type_id' => 'required|exists:subscription_types,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,paid,partial,overdue',
        ]);

        Subscription::create($validated);

        return response()->json(['success' => true, 'message' => 'تم إضافة الاشتراك بنجاح']);
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
        $subscription = Subscription::findOrFail($id);
        $apartments = Apartment::all();
        $subscriptionTypes = SubscriptionType::where('is_active', true)->get();
        return response()->json(['html' => view('subscriptions.edit', compact('subscription', 'apartments', 'subscriptionTypes'))->render()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subscription = Subscription::findOrFail($id);

        $validated = $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'subscription_type_id' => 'required|exists:subscription_types,id',
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,paid,partial,overdue',
        ]);

        $subscription->update($validated);

        return response()->json(['success' => true, 'message' => 'تم تعديل الاشتراك بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الاشتراك بنجاح']);
    }

    /**
     * Mark subscription as paid (from building fund)
     */
    public function markPaid($id)
    {
        DB::beginTransaction();
        try {
            $subscription = Subscription::with(['apartment', 'subscriptionType'])->findOrFail($id);
            $tenant = auth()->user()->tenant;

            // Check building fund balance
            $currentBalance = BuildingFundTransaction::getCurrentBalance($tenant->id);
            if ($currentBalance < $subscription->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'رصيد حساب العمارة غير كافٍ. الرصيد الحالي: ' . number_format($currentBalance, 2) . ' ج.م'
                ], 422);
            }

            // Update subscription
            $subscription->update([
                'status' => 'paid',
                'paid_amount' => $subscription->amount,
                'paid_at' => now(),
            ]);

            // Deduct from building fund
            $description = 'دفع اشتراك ' . ($subscription->subscriptionType ? $subscription->subscriptionType->name : '') .
                          ' - شقة ' . ($subscription->apartment ? $subscription->apartment->number : '') .
                          ' - ' . $subscription->year . '/' . $subscription->month;

            BuildingFundTransaction::addExpense(
                $tenant->id,
                $subscription->amount,
                'subscription',
                $subscription->id,
                $description
            );

            // Notify all residents
            $residents = User::where('tenant_id', $tenant->id)
                ->where('role', 'resident')
                ->get();

            foreach ($residents as $resident) {
                NotificationController::notifyUser(
                    $resident->id,
                    'building_expense',
                    $subscription->id,
                    'تم دفع اشتراك من حساب العمارة',
                    $description . ' - المبلغ: ' . number_format($subscription->amount, 2) . ' ج.م'
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم تسجيل الدفع وخصمه من حساب العمارة بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show payment recording page for a specific month/year
     */
    public function payments(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $subscriptionTypeId = $request->input('subscription_type_id');
        $status = $request->input('status');

        if ($request->ajax()) {
            // جلب جميع الاشتراكات للشهر المحدد
            $query = Subscription::with(['apartment.resident', 'subscriptionType'])
                ->where('year', $year)
                ->where('month', $month);

            if ($subscriptionTypeId) {
                $query->where('subscription_type_id', $subscriptionTypeId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $subscriptions = $query->get();

            // تجميع الاشتراكات حسب الشقة
            $apartmentGroups = $subscriptions->groupBy('apartment_id')->map(function ($apartmentSubs) {
                $apartment = $apartmentSubs->first()->apartment;
                $totalAmount = $apartmentSubs->sum('amount');
                $paidAmount = $apartmentSubs->sum('paid_amount');
                $remaining = $totalAmount - $paidAmount;

                // تحديد الحالة العامة
                $allPaid = $apartmentSubs->every(fn($s) => $s->status === 'paid');
                $anyOverdue = $apartmentSubs->contains(fn($s) => $s->status === 'overdue');
                $anyPending = $apartmentSubs->contains(fn($s) => $s->status === 'pending');

                if ($allPaid) {
                    $overallStatus = 'paid';
                } elseif ($anyOverdue) {
                    $overallStatus = 'overdue';
                } elseif ($paidAmount > 0) {
                    $overallStatus = 'partial';
                } else {
                    $overallStatus = 'pending';
                }

                return [
                    'apartment_id' => $apartment->id,
                    'apartment_number' => $apartment->number,
                    'resident_name' => $apartment->resident ? $apartment->resident->name : '-',
                    'subscriptions_count' => $apartmentSubs->count(),
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'remaining' => $remaining,
                    'status' => $overallStatus,
                ];
            })->values();

            return DataTables::of($apartmentGroups)
                ->addColumn('status_label', function ($row) {
                    $statusLabels = [
                        'pending' => '<span class="badge bg-warning">معلق</span>',
                        'paid' => '<span class="badge bg-success">مدفوع بالكامل</span>',
                        'partial' => '<span class="badge bg-info">مدفوع جزئياً</span>',
                        'overdue' => '<span class="badge bg-danger">متأخر</span>',
                    ];
                    return $statusLabels[$row['status']] ?? '<span class="badge bg-secondary">غير محدد</span>';
                })
                ->addColumn('amounts', function ($row) {
                    return '<div>' .
                           '<strong>الإجمالي:</strong> ' . number_format($row['total_amount'], 2) . ' ج.م<br>' .
                           '<strong>المدفوع:</strong> ' . number_format($row['paid_amount'], 2) . ' ج.م<br>' .
                           '<strong>المتبقي:</strong> <span class="text-danger">' . number_format($row['remaining'], 2) . ' ج.م</span>' .
                           '</div>';
                })
                ->addColumn('action', function ($row) use ($year, $month) {
                    return '<button class="btn btn-sm btn-primary view-details-btn"
                                data-apartment-id="' . $row['apartment_id'] . '"
                                data-year="' . $year . '"
                                data-month="' . $month . '">
                                <i class="fas fa-eye me-1"></i> عرض التفاصيل
                            </button>';
                })
                ->rawColumns(['status_label', 'amounts', 'action'])
                ->make(true);
        }

        $subscriptionTypes = SubscriptionType::where('is_active', true)->get();
        return view('subscriptions.payments', compact('year', 'month', 'subscriptionTypes'));
    }

    /**
     * Send late payment reminders
     */
    public function sendLatePaymentReminders(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $subscriptionTypeId = $request->input('subscription_type_id');

        $query = Subscription::with(['apartment.resident', 'subscriptionType'])
            ->where('year', $year)
            ->where('month', $month)
            ->whereIn('status', ['pending', 'overdue', 'partial']);

        if ($subscriptionTypeId) {
            $query->where('subscription_type_id', $subscriptionTypeId);
        }

        $lateSubscriptions = $query->get();
        $count = 0;

        foreach ($lateSubscriptions as $subscription) {
            $apartment = $subscription->apartment;
            if ($apartment && $apartment->resident) {
                NotificationController::notifyUser(
                    $apartment->resident->id,
                    'late_payment',
                    $subscription->id,
                    'تذكير بالدفع المتأخر',
                    'يرجى تسديد اشتراك ' . ($subscription->subscriptionType ? $subscription->subscriptionType->name : '') . ' لشهر ' . $subscription->month . '/' . $subscription->year . ' بقيمة ' . number_format($subscription->amount, 2) . ' ج.م'
                );
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال ' . $count . ' تذكير للمتأخرين عن الدفع'
        ]);
    }

    /**
     * Get apartment subscription details for a specific month
     */
    public function getApartmentSubscriptions(Request $request, $apartmentId)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $subscriptions = Subscription::with(['subscriptionType', 'apartment'])
            ->where('apartment_id', $apartmentId)
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        return response()->json([
            'subscriptions' => $subscriptions,
            'apartment' => $subscriptions->first()->apartment ?? null
        ]);
    }
}
