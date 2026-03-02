<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'tenant_code',
        'name',
        'address',
        'subscription_price',
        'units_count',
        'subscription_expires_at',
        'subscription_status',
        'last_subscription_reminder',
        'admin_name',
        'admin_email',
        'admin_phone',
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
        'last_subscription_reminder' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}
