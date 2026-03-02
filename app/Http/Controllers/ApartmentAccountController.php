<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\ApartmentAccountTransaction;
use App\Services\ApartmentAccountService;
use Illuminate\Http\Request;

class ApartmentAccountController extends Controller
{
    public function __construct(
        private ApartmentAccountService $accountService
    ) {}

    // =====================================================================
    // ADMIN: View apartment wallet
    // GET /apartments/{id}/account
    // =====================================================================
    public function show(int $id)
    {
        $apartment = Apartment::with('resident')->findOrFail($id);

        $balance = ApartmentAccountTransaction::getCurrentBalance($apartment->id);

        $transactions = ApartmentAccountTransaction::withoutGlobalScopes()
            ->where('apartment_id', $apartment->id)
            ->with('creator')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('apartments.account', compact('apartment', 'balance', 'transactions'));
    }

    // =====================================================================
    // ADMIN: Deposit into apartment wallet
    // POST /apartments/{id}/account/deposit
    // =====================================================================
    public function deposit(Request $request, int $id)
    {
        $apartment = Apartment::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes'  => 'nullable|string|max:500',
        ]);

        try {
            $this->accountService->deposit(
                apartment: $apartment,
                amount:    (float) $validated['amount'],
                createdBy: auth()->id(),
                notes:     $validated['notes'] ?? ''
            );

            return response()->json([
                'success'     => true,
                'message'     => 'تم الإيداع بنجاح وتم تسوية المستحقات المعلقة تلقائياً',
                'new_balance' => ApartmentAccountTransaction::getCurrentBalance($apartment->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    // =====================================================================
    // RESIDENT: My account page
    // GET /my-account
    // =====================================================================
    public function myAccount()
    {
        $user      = auth()->user();
        $apartment = Apartment::findOrFail($user->apartment_id);

        $balance = ApartmentAccountTransaction::getCurrentBalance($apartment->id);

        $transactions = ApartmentAccountTransaction::withoutGlobalScopes()
            ->where('apartment_id', $apartment->id)
            ->with('creator')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('dashboard.my-account', compact('apartment', 'balance', 'transactions'));
    }
}
