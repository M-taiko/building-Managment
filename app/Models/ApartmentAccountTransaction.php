<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApartmentAccountTransaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'apartment_id',
        'transaction_type',
        'source_type',
        'source_id',
        'amount',
        'balance_after',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the current wallet balance for an apartment.
     * Uses withoutGlobalScopes to avoid TenantScope issues when called from jobs/commands.
     */
    public static function getCurrentBalance(int $apartmentId): float
    {
        $last = static::withoutGlobalScopes()
            ->where('apartment_id', $apartmentId)
            ->orderBy('id', 'desc')
            ->first();

        return $last ? (float) $last->balance_after : 0.0;
    }

    /**
     * Add a credit (deposit) to the apartment wallet.
     */
    public static function addCredit(
        int    $tenantId,
        int    $apartmentId,
        float  $amount,
        string $sourceType,
        ?int   $sourceId,
        ?string $description,
        int    $createdBy
    ): static {
        $balance = static::getCurrentBalance($apartmentId) + $amount;

        return static::create([
            'tenant_id'        => $tenantId,
            'apartment_id'     => $apartmentId,
            'transaction_type' => 'credit',
            'source_type'      => $sourceType,
            'source_id'        => $sourceId,
            'amount'           => $amount,
            'balance_after'    => $balance,
            'description'      => $description,
            'created_by'       => $createdBy,
        ]);
    }

    /**
     * Add a debit (auto-payment) from the apartment wallet.
     */
    public static function addDebit(
        int    $tenantId,
        int    $apartmentId,
        float  $amount,
        string $sourceType,
        ?int   $sourceId,
        ?string $description,
        int    $createdBy
    ): static {
        $balance = static::getCurrentBalance($apartmentId) - $amount;

        return static::create([
            'tenant_id'        => $tenantId,
            'apartment_id'     => $apartmentId,
            'transaction_type' => 'debit',
            'source_type'      => $sourceType,
            'source_id'        => $sourceId,
            'amount'           => $amount,
            'balance_after'    => $balance,
            'description'      => $description,
            'created_by'       => $createdBy,
        ]);
    }
}
