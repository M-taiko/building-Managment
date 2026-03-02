<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ExpenseShare extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'expense_id',
        'apartment_id',
        'share_amount',
        'paid_amount', // المبلغ المدفوع فعلياً
        'paid',
        'paid_at',
    ];

    protected $casts = [
        'share_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * العلاقة مع المدفوعات
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * الحصول على المتبقي من المبلغ
     */
    public function getRemainingAmountAttribute()
    {
        return $this->share_amount - ($this->paid_amount ?? 0);
    }

    /**
     * الحصول على حالة الدفع
     */
    public function getPaymentStatusAttribute()
    {
        if ($this->paid) {
            return 'paid';
        }

        if ($this->paid_amount > 0) {
            return 'partial';
        }

        return 'unpaid';
    }

    /**
     * تسجيل دفعة
     */
    public function recordPayment($amount, $recordedBy, $paymentData = [])
    {
        $payment = Payment::create([
            'tenant_id' => $this->tenant_id,
            'apartment_id' => $this->apartment_id,
            'expense_share_id' => $this->id,
            'amount' => $amount,
            'payment_date' => $paymentData['payment_date'] ?? now(),
            'payment_method' => $paymentData['payment_method'] ?? 'cash',
            'reference_number' => $paymentData['reference_number'] ?? null,
            'notes' => $paymentData['notes'] ?? null,
            'recorded_by' => $recordedBy,
        ]);

        // تحديث المبلغ المدفوع
        $this->paid_amount = ($this->paid_amount ?? 0) + $amount;

        // تحديث حالة الدفع
        if ($this->paid_amount >= $this->share_amount) {
            $this->paid = true;
            $this->paid_at = now();
        }

        $this->save();

        return $payment;
    }
}
