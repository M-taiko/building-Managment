<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'apartment_id',
        'title',
        'description',
        'status',
        'priority',
        'created_by',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
