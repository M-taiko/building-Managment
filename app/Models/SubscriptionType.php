<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SubscriptionType extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'amount',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * الشقق المرتبطة بهذا النوع من الاشتراكات (Many-to-Many)
     */
    public function apartments()
    {
        return $this->belongsToMany(Apartment::class, 'apartment_subscription_type')
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
