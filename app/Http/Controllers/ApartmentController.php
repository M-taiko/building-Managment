<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $apartments = Apartment::with('tenant')->select('apartments.*');

            return DataTables::of($apartments)
                ->addColumn('action', function ($apartment) {
                    $walletBalance = \App\Models\ApartmentAccountTransaction::getCurrentBalance($apartment->id);
                    $badge = $walletBalance > 0
                        ? '<span class="badge bg-success ms-1">' . number_format($walletBalance, 0) . '</span>'
                        : '';
                    return '
                        <a href="' . route('apartments.account', $apartment->id) . '"
                           class="btn btn-sm btn-outline-success mb-1" title="حساب الشقة">
                            <i class="fas fa-wallet"></i>' . $badge . '
                        </a>
                        <button class="btn btn-sm btn-info edit-btn mb-1" data-id="' . $apartment->id . '">تعديل</button>
                        <button class="btn btn-sm btn-danger delete-btn mb-1" data-id="' . $apartment->id . '">حذف</button>
                    ';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('apartments.index');
    }

    public function create()
    {
        $tenant = auth()->user()->tenant;
        $currentApartmentsCount = $tenant->apartments()->count();
        $remainingUnits = $tenant->units_count - $currentApartmentsCount;
        $allSubscriptionTypes = SubscriptionType::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'html' => view('apartments.create', compact('tenant', 'remainingUnits', 'allSubscriptionTypes'))->render(),
            'remainingUnits' => $remainingUnits
        ]);
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $currentApartmentsCount = $tenant->apartments()->count();

        // التحقق من عدد الوحدات المسموح بها
        if ($currentApartmentsCount >= $tenant->units_count) {
            return response()->json([
                'success' => false,
                'message' => 'لقد وصلت للحد الأقصى من الوحدات المسموح بها (' . $tenant->units_count . ' وحدة). يرجى ترقية اشتراكك.'
            ], 422);
        }

        $validated = $request->validate([
            'number' => 'required|string|max:255',
            'floor' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'type' => 'required|in:residential,commercial',
            'share_type' => 'required|in:equal,custom',
            'custom_share_percentage' => 'required_if:share_type,custom|nullable|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
            // بيانات المستخدم (اختياري)
            'create_user' => 'nullable|boolean',
            'user_name' => 'required_if:create_user,1|nullable|string|max:255',
            'user_email' => 'required_if:create_user,1|nullable|email|unique:users,email',
            'user_phone' => 'nullable|string|max:20',
            'user_password' => 'required_if:create_user,1|nullable|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            // إنشاء الشقة
            $apartment = Apartment::create($validated);

            // ربط أنواع الاشتراكات المختارة بالشقة
            if ($request->has('subscription_types')) {
                $apartment->subscriptionTypes()->attach($request->subscription_types);

                // توليد الاشتراكات للشهر الحالي تلقائيًا
                $this->generateSubscriptionsForApartment($apartment);
            }

            // إنشاء مستخدم للمالك إذا تم تفعيل الخيار
            if ($request->create_user) {
                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'apartment_id' => $apartment->id,
                    'name' => $request->user_name,
                    'email' => $request->user_email,
                    'phone' => $request->user_phone,
                    'password' => Hash::make($request->user_password),
                    'role' => 'resident',
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الشقة بنجاح' . ($request->create_user ? ' وتم إنشاء حساب المالك' : '')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة الشقة: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        $apartment = Apartment::findOrFail($id);
        $tenant = auth()->user()->tenant;
        $allSubscriptionTypes = SubscriptionType::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get();
        $apartmentSubscriptionIds = $apartment->subscriptionTypes()->pluck('subscription_type_id')->toArray();

        return response()->json(['html' => view('apartments.edit', compact('apartment', 'allSubscriptionTypes', 'apartmentSubscriptionIds'))->render()]);
    }

    public function update(Request $request, $id)
    {
        $apartment = Apartment::with('resident')->findOrFail($id);

        $validated = $request->validate([
            'number' => 'required|string|max:255',
            'floor' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'type' => 'required|in:residential,commercial',
            'share_type' => 'required|in:equal,custom',
            'custom_share_percentage' => 'required_if:share_type,custom|nullable|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
            // بيانات المستخدم (اختياري)
            'update_user' => 'nullable|boolean',
            'user_name' => 'required_if:update_user,1|nullable|string|max:255',
            'user_email' => 'required_if:update_user,1|nullable|email|unique:users,email,' . ($apartment->resident ? $apartment->resident->id : 'NULL'),
            'user_phone' => 'nullable|string|max:20',
            'user_password' => 'nullable|string|min:6',
        ]);

        DB::beginTransaction();
        try {
            $apartment->update($validated);

            // تحديث أنواع الاشتراكات المرتبطة بالشقة
            if ($request->has('subscription_types')) {
                $apartment->subscriptionTypes()->sync($request->subscription_types);

                // توليد الاشتراكات للشهر الحالي تلقائيًا
                $this->generateSubscriptionsForApartment($apartment);
            } else {
                // إذا لم يتم إرسال أي اشتراكات، نحذف جميع الاشتراكات المرتبطة
                $apartment->subscriptionTypes()->detach();
            }

            // تحديث بيانات المستخدم إذا موجود
            if ($request->update_user && $apartment->resident) {
                $userData = [
                    'name' => $request->user_name,
                    'email' => $request->user_email,
                    'phone' => $request->user_phone,
                ];

                if ($request->user_password) {
                    $userData['password'] = Hash::make($request->user_password);
                }

                $apartment->resident->update($userData);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم تعديل الشقة بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تعديل الشقة: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $apartment = Apartment::findOrFail($id);
        $apartment->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الشقة بنجاح']);
    }

    /**
     * توليد الاشتراكات الشهرية للشقة للشهر الحالي
     */
    private function generateSubscriptionsForApartment(Apartment $apartment)
    {
        // إعادة تحميل العلاقات للتأكد من الحصول على أحدث البيانات
        $apartment->load('subscriptionTypes', 'tenant');

        $tenant = $apartment->tenant;
        $year = now()->year;
        $month = now()->month;

        // الحصول على أنواع الاشتراكات النشطة المرتبطة بالشقة
        $subscriptionTypes = $apartment->subscriptionTypes()
            ->wherePivot('is_active', true)
            ->get();

        foreach ($subscriptionTypes as $type) {
            // التحقق من عدم وجود اشتراك بالفعل لهذا الشهر
            $existingSubscription = Subscription::where('apartment_id', $apartment->id)
                ->where('subscription_type_id', $type->id)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$existingSubscription) {
                // حساب نصيب هذه الشقة من الاشتراك
                $apartmentShare = $this->calculateApartmentShare($type, $apartment);

                // إنشاء الاشتراك
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'apartment_id' => $apartment->id,
                    'subscription_type_id' => $type->id,
                    'year' => $year,
                    'month' => $month,
                    'amount' => $apartmentShare,
                    'paid_amount' => 0,
                    'status' => 'pending',
                ]);
            }

            // إعادة حساب جميع الاشتراكات لهذا النوع في الشهر الحالي
            $this->recalculateSubscriptions($type->id, $year, $month);
        }

        // Auto-settle new subscriptions from apartment wallet balance
        if (\App\Models\ApartmentAccountTransaction::getCurrentBalance($apartment->id) > 0) {
            $accountService = app(\App\Services\ApartmentAccountService::class);
            $accountService->applyBalance($apartment, auth()->id());
        }
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
    private function recalculateSubscriptions($subscriptionTypeId, $year, $month)
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
