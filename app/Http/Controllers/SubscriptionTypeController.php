<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionType;
use App\Models\Subscription;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SubscriptionTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $subscriptionTypes = SubscriptionType::select('subscription_types.*');

            return DataTables::of($subscriptionTypes)
                ->addColumn('is_active_label', function ($type) {
                    return $type->is_active
                        ? '<span class="badge bg-success">نشط</span>'
                        : '<span class="badge bg-secondary">غير نشط</span>';
                })
                ->addColumn('formatted_amount', function ($type) {
                    return number_format($type->amount, 2) . ' ج.م';
                })
                ->addColumn('action', function ($type) {
                    return '
                        <button class="btn btn-sm btn-info edit-btn" data-id="' . $type->id . '">تعديل</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="' . $type->id . '">حذف</button>
                    ';
                })
                ->rawColumns(['is_active_label', 'action'])
                ->make(true);
        }

        return view('subscription-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json(['html' => view('subscription-types.create')->render()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        SubscriptionType::create($validated);

        return response()->json(['success' => true, 'message' => 'تم إضافة نوع الاشتراك بنجاح']);
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
        $subscriptionType = SubscriptionType::findOrFail($id);
        return response()->json(['html' => view('subscription-types.edit', compact('subscriptionType'))->render()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $subscriptionType = SubscriptionType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);

        $oldAmount = $subscriptionType->amount;
        $subscriptionType->update($validated);

        // إذا تغير المبلغ، نعيد حساب جميع الاشتراكات غير المدفوعة للشهر الحالي
        if ($oldAmount != $validated['amount']) {
            $this->recalculateSubscriptionsForType($subscriptionType->id, now()->year, now()->month);
        }

        return response()->json(['success' => true, 'message' => 'تم تعديل نوع الاشتراك بنجاح']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $subscriptionType = SubscriptionType::findOrFail($id);
        $subscriptionType->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف نوع الاشتراك بنجاح']);
    }

    /**
     * حساب نصيب الشقة من اشتراك معين
     */
    private function calculateApartmentShare(SubscriptionType $subscriptionType, Apartment $apartment)
    {
        $totalAmount = $subscriptionType->amount;

        // الحصول على جميع الشقق المشتركة في هذا النوع
        $allSubscribedApartments = Apartment::where('tenant_id', $apartment->tenant_id)
            ->where('is_active', true)
            ->whereHas('subscriptionTypes', function ($query) use ($subscriptionType) {
                $query->where('subscription_type_id', $subscriptionType->id)
                    ->where('apartment_subscription_type.is_active', true);
            })
            ->get();

        $count = $allSubscribedApartments->count();

        if ($count === 0) {
            return 0;
        }

        // إذا كانت جميع الشقق من نوع "التساوي"، نقسم بالتساوي
        $allEqual = $allSubscribedApartments->every(fn($apt) => $apt->share_type === 'equal');

        if ($allEqual) {
            return $totalAmount / $count;
        }

        // حساب التوزيع بناءً على النسب
        $customApartments = $allSubscribedApartments->where('share_type', 'custom');
        $equalApartments = $allSubscribedApartments->where('share_type', 'equal');

        $totalCustomPercentage = $customApartments->sum('custom_share_percentage');
        $equalCount = $equalApartments->count();

        // النسبة المتبقية للشقق ذات التوزيع المتساوي
        $remainingPercentage = max(0, 100 - $totalCustomPercentage);

        if ($apartment->share_type === 'equal') {
            if ($equalCount > 0) {
                $perEqualPercentage = $remainingPercentage / $equalCount;
                return $totalAmount * ($perEqualPercentage / 100);
            }
            return 0;
        } else {
            return $totalAmount * ($apartment->custom_share_percentage / 100);
        }
    }

    /**
     * إعادة حساب جميع الاشتراكات لنوع معين في شهر معين
     */
    private function recalculateSubscriptionsForType($subscriptionTypeId, $year, $month)
    {
        $subscriptionType = SubscriptionType::findOrFail($subscriptionTypeId);

        // الحصول على جميع الاشتراكات لهذا النوع في الشهر المحدد
        $subscriptions = Subscription::with('apartment')
            ->where('subscription_type_id', $subscriptionTypeId)
            ->where('year', $year)
            ->where('month', $month)
            ->where('status', '!=', 'paid') // فقط الاشتراكات غير المدفوعة بالكامل
            ->get();

        foreach ($subscriptions as $subscription) {
            // حساب النصيب الجديد
            $newAmount = $this->calculateApartmentShare($subscriptionType, $subscription->apartment);

            // تحديث المبلغ فقط إذا تغير
            if ($subscription->amount != $newAmount) {
                // إذا كان هناك مبلغ مدفوع جزئياً، نحتفظ به ونعيد حساب الحالة
                $paidAmount = $subscription->paid_amount ?? 0;

                // تحديد الحالة الجديدة
                $newStatus = $subscription->status;
                if ($paidAmount >= $newAmount) {
                    $newStatus = 'paid';
                } elseif ($paidAmount > 0) {
                    $newStatus = 'partial';
                }

                $subscription->update([
                    'amount' => $newAmount,
                    'status' => $newStatus,
                ]);
            }
        }
    }
}
