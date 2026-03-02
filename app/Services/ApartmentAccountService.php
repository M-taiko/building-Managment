<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\ApartmentAccountTransaction;
use App\Models\BuildingFundTransaction;
use App\Models\ExpenseShare;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class ApartmentAccountService
{
    /**
     * Deposit money into an apartment's wallet.
     * Also credits the building fund and triggers auto-settlement.
     */
    public function deposit(
        Apartment $apartment,
        float     $amount,
        int       $createdBy,
        string    $notes = ''
    ): ApartmentAccountTransaction {
        return DB::transaction(function () use ($apartment, $amount, $createdBy, $notes) {

            // 1. Credit the apartment wallet ledger
            $credit = ApartmentAccountTransaction::addCredit(
                tenantId:    $apartment->tenant_id,
                apartmentId: $apartment->id,
                amount:      $amount,
                sourceType:  'deposit',
                sourceId:    null,
                description: $notes ?: "إيداع في حساب الشقة {$apartment->number}",
                createdBy:   $createdBy
            );

            // 2. Record the cash as income in the building fund
            BuildingFundTransaction::addIncome(
                $apartment->tenant_id,
                $amount,
                'direct_payment',
                $credit->id,
                "إيداع مسبق - شقة {$apartment->number}" . ($notes ? " ({$notes})" : '')
            );

            // 3. Sweep any pending obligations from the wallet balance
            $this->applyBalance($apartment, $createdBy);

            return $credit;
        });
    }

    /**
     * Apply available wallet balance to all unpaid obligations.
     * Called after deposit AND after new expense shares / subscriptions are created.
     *
     * Order: expense shares first (oldest first), then subscriptions (oldest first).
     * Partial payments are supported.
     */
    public function applyBalance(Apartment $apartment, int $createdBy): void
    {
        $balance = ApartmentAccountTransaction::getCurrentBalance($apartment->id);

        if ($balance <= 0) {
            return;
        }

        // ---- Settle unpaid ExpenseShares (oldest first) ----
        $unpaidShares = ExpenseShare::withoutGlobalScopes()
            ->where('apartment_id', $apartment->id)
            ->where('paid', false)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($unpaidShares as $share) {
            if ($balance <= 0) break;

            $remaining = (float) $share->remaining_amount;
            if ($remaining <= 0) continue;

            $toPay = min($balance, $remaining);

            // recordPayment creates a Payment record and updates paid_amount/paid
            $share->recordPayment($toPay, $createdBy, [
                'payment_date'   => now()->toDateString(),
                'payment_method' => 'account_balance',
                'notes'          => 'خصم تلقائي من رصيد الشقة',
            ]);

            // Debit the apartment wallet
            ApartmentAccountTransaction::addDebit(
                tenantId:    $apartment->tenant_id,
                apartmentId: $apartment->id,
                amount:      $toPay,
                sourceType:  'auto_expense_payment',
                sourceId:    $share->id,
                description: "سداد تلقائي - مصروف #{$share->expense_id} - شقة {$apartment->number}",
                createdBy:   $createdBy
            );

            $balance -= $toPay;
        }

        // ---- Settle unpaid/partial Subscriptions (oldest first) ----
        $unpaidSubs = Subscription::withoutGlobalScopes()
            ->where('apartment_id', $apartment->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        foreach ($unpaidSubs as $sub) {
            if ($balance <= 0) break;

            $paidAmount = (float) ($sub->paid_amount ?? 0);
            $remaining  = (float) $sub->amount - $paidAmount;
            if ($remaining <= 0) continue;

            $toPay = min($balance, $remaining);

            $newPaidAmount = $paidAmount + $toPay;
            $newStatus     = ($newPaidAmount >= (float) $sub->amount) ? 'paid' : 'partial';

            $sub->update([
                'paid_amount' => $newPaidAmount,
                'status'      => $newStatus,
                'paid_at'     => $newStatus === 'paid' ? now() : $sub->paid_at,
            ]);

            // Debit the apartment wallet
            ApartmentAccountTransaction::addDebit(
                tenantId:    $apartment->tenant_id,
                apartmentId: $apartment->id,
                amount:      $toPay,
                sourceType:  'auto_subscription_payment',
                sourceId:    $sub->id,
                description: "سداد تلقائي - اشتراك {$sub->month}/{$sub->year} - شقة {$apartment->number}",
                createdBy:   $createdBy
            );

            $balance -= $toPay;
        }
    }
}
