<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'type', // monthly or one_time
        'month', // للمصروفات الشهرية
        'year', // للمصروفات الشهرية
        'amount',
        'distribution_type',
        'apartment_id',
        'is_one_time',
        'date',
        'status', // pending or distributed
        'created_by',
        'is_recurring',
        'recurrence_type', // one_time, monthly, yearly
        'subscription_type_id',
        'next_occurrence_date',
        'last_generated_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'is_one_time' => 'boolean',
        'month' => 'integer',
        'year' => 'integer',
        'is_recurring' => 'boolean',
        'next_occurrence_date' => 'date',
        'last_generated_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function shares()
    {
        return $this->hasMany(ExpenseShare::class);
    }

    public function subscriptionType()
    {
        return $this->belongsTo(SubscriptionType::class);
    }

    /**
     * توزيع المصروف على الوحدات
     */
    public function distribute()
    {
        if ($this->status === 'distributed') {
            return false;
        }

        $apartments = $this->distribution_type === 'all'
            ? Apartment::where('tenant_id', $this->tenant_id)->where('is_active', true)->get()
            : Apartment::where('id', $this->apartment_id)->get();

        if ($apartments->isEmpty()) {
            return false;
        }

        // حساب نصيب كل وحدة
        if ($this->distribution_type === 'all') {
            // حساب بناءً على share_type
            $totalPercentage = $apartments->sum(function ($apt) {
                return $apt->share_type === 'custom' ? $apt->custom_share_percentage : 100;
            });

            foreach ($apartments as $apartment) {
                $percentage = $apartment->share_type === 'custom'
                    ? $apartment->custom_share_percentage
                    : (100 / $apartments->count());

                $shareAmount = ($this->amount * $percentage) / $totalPercentage;

                ExpenseShare::create([
                    'tenant_id' => $this->tenant_id,
                    'expense_id' => $this->id,
                    'apartment_id' => $apartment->id,
                    'share_amount' => $shareAmount,
                    'paid' => false,
                ]);
            }
        } else {
            // توزيع على وحدة واحدة
            ExpenseShare::create([
                'tenant_id' => $this->tenant_id,
                'expense_id' => $this->id,
                'apartment_id' => $this->apartment_id,
                'share_amount' => $this->amount,
                'paid' => false,
            ]);
        }

        $this->update(['status' => 'distributed']);

        // Auto-settle from apartment wallet balances
        $accountService = app(\App\Services\ApartmentAccountService::class);
        foreach ($apartments as $apartment) {
            if (\App\Models\ApartmentAccountTransaction::getCurrentBalance($apartment->id) > 0) {
                $accountService->applyBalance($apartment, $this->created_by ?? auth()->id());
            }
        }

        return true;
    }

    /**
     * الحصول على اسم الشهر بالعربي
     */
    public function getMonthNameAttribute()
    {
        if (!$this->month) return null;

        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        return $months[$this->month] ?? null;
    }
}
