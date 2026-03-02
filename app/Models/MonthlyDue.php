<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class MonthlyDue extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'apartment_id',
        'year',
        'month',
        'amount',
        'paid_amount',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get remaining amount to be paid
     */
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->paid_amount;
    }
}
