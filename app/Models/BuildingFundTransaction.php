<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BuildingFundTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'transaction_type',
        'source_type',
        'source_id',
        'expense_type',
        'expense_id',
        'amount',
        'balance_after',
        'wallet_type',
        'custody_user_id',
        'custody_notes',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the current building fund balance (all wallet types)
     */
    public static function getCurrentBalance($tenantId)
    {
        $lastTransaction = self::where('tenant_id', $tenantId)
            ->orderBy('id', 'desc')
            ->first();

        return $lastTransaction ? $lastTransaction->balance_after : 0;
    }

    /**
     * Get cash wallet balance
     */
    public static function getCashBalance($tenantId): float
    {
        $lastTransaction = self::where('tenant_id', $tenantId)
            ->where('wallet_type', '=', 'cash')
            ->orderBy('id', 'desc')
            ->first();

        // If no cash-specific transaction, return 0
        return $lastTransaction ? (float) $lastTransaction->balance_after : 0;
    }

    /**
     * Get bank wallet balance
     */
    public static function getBankBalance($tenantId): float
    {
        $lastTransaction = self::where('tenant_id', $tenantId)
            ->where('wallet_type', '=', 'bank')
            ->orderBy('id', 'desc')
            ->first();

        // If no bank-specific transaction, return 0
        return $lastTransaction ? (float) $lastTransaction->balance_after : 0;
    }

    /**
     * Get active custodies (عهدات)
     */
    public static function getActiveCustodies($tenantId)
    {
        return self::where('tenant_id', $tenantId)
            ->whereNotNull('custody_user_id')
            ->with('creator')
            ->get();
    }

    /**
     * Add income to building fund
     */
    public static function addIncome($tenantId, $amount, $sourceType, $sourceId, $description = null)
    {
        $currentBalance = self::getCurrentBalance($tenantId);
        $newBalance = $currentBalance + $amount;

        return self::create([
            'tenant_id' => $tenantId,
            'transaction_type' => 'income',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Add expense from building fund (with balance validation)
     */
    public static function addExpense($tenantId, $amount, $expenseType, $expenseId, $description = null)
    {
        $currentBalance = self::getCurrentBalance($tenantId);

        // Prevent negative balance
        if ($currentBalance < $amount) {
            throw new \Exception(
                'رصيد حساب العمارة غير كافٍ. الرصيد الحالي: ' .
                number_format($currentBalance, 2) . ' ج.م والمبلغ المطلوب: ' .
                number_format($amount, 2) . ' ج.م'
            );
        }

        $newBalance = $currentBalance - $amount;

        return self::create([
            'tenant_id' => $tenantId,
            'transaction_type' => 'expense',
            'expense_type' => $expenseType,
            'expense_id' => $expenseId,
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description,
            'created_by' => auth()->id(),
        ]);
    }
}
