<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Apartment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'number',
        'floor',
        'owner_name',
        'type', // residential or commercial
        'share_type', // equal or custom
        'custom_share_percentage',
        'is_active',
    ];

    protected $casts = [
        'custom_share_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function resident()
    {
        return $this->hasOne(User::class)->where('role', 'resident');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function expenseShares()
    {
        return $this->hasMany(ExpenseShare::class);
    }

    /**
     * العلاقة مع المدفوعات
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * الحصول على إجمالي المتأخرات
     */
    public function getTotalArrearsAttribute()
    {
        return $this->expenseShares()
            ->where('paid', false)
            ->sum('share_amount') - $this->expenseShares()
            ->where('paid', false)
            ->sum('paid_amount');
    }

    /**
     * الحصول على عدد الحصص غير المدفوعة
     */
    public function getUnpaidSharesCountAttribute()
    {
        return $this->expenseShares()
            ->where('paid', false)
            ->count();
    }

    /**
     * أنواع الاشتراكات المرتبطة بالشقة (Many-to-Many)
     */
    public function subscriptionTypes()
    {
        return $this->belongsToMany(SubscriptionType::class, 'apartment_subscription_type')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * أنواع الاشتراكات النشطة فقط
     */
    public function activeSubscriptionTypes()
    {
        return $this->subscriptionTypes()->wherePivot('is_active', true);
    }

    /**
     * معاملات حساب الشقة (المحفظة)
     */
    public function accountTransactions()
    {
        return $this->hasMany(ApartmentAccountTransaction::class);
    }

    /**
     * الرصيد الحالي لحساب الشقة
     */
    public function getAccountBalanceAttribute(): float
    {
        return ApartmentAccountTransaction::getCurrentBalance($this->id);
    }
}
