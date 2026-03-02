<?php

namespace App\Http\Controllers;

use App\Models\BuildingFundTransaction;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BuildingFundController extends Controller
{
    /**
     * عرض صفحة حساب العمارة
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        // الحصول على الرصيد الحالي
        $currentBalance = BuildingFundTransaction::getCurrentBalance($tenant->id);

        if ($request->ajax()) {
            $query = BuildingFundTransaction::with('creator')
                ->where('tenant_id', $tenant->id);

            // الفلاتر
            if ($request->has('transaction_type') && $request->transaction_type != '') {
                $query->where('transaction_type', $request->transaction_type);
            }
            if ($request->has('expense_type') && $request->expense_type != '') {
                $query->where('expense_type', $request->expense_type);
            }

            $transactions = $query->orderBy('id', 'desc')->get();

            return DataTables::of($transactions)
                ->addColumn('date', function ($transaction) {
                    return $transaction->created_at->format('Y-m-d H:i');
                })
                ->addColumn('type_badge', function ($transaction) {
                    if ($transaction->transaction_type === 'income') {
                        return '<span class="badge bg-success"><i class="fas fa-arrow-down"></i> إيراد</span>';
                    } else {
                        return '<span class="badge bg-danger"><i class="fas fa-arrow-up"></i> مصروف</span>';
                    }
                })
                ->addColumn('category', function ($transaction) {
                    if ($transaction->transaction_type === 'income') {
                        $types = [
                            'monthly_due' => 'مستحق شهري',
                            'direct_payment' => 'دفعة مباشرة',
                            'subscription_payment' => 'دفع اشتراك',
                        ];
                        return $types[$transaction->source_type] ?? '-';
                    } else {
                        $types = [
                            'subscription' => 'اشتراك',
                            'maintenance' => 'صيانة',
                            'other' => 'مصروف آخر',
                        ];
                        return $types[$transaction->expense_type] ?? '-';
                    }
                })
                ->addColumn('amount_formatted', function ($transaction) {
                    $color = $transaction->transaction_type === 'income' ? 'text-success' : 'text-danger';
                    $sign = $transaction->transaction_type === 'income' ? '+' : '-';
                    return '<span class="' . $color . ' fw-bold">' . $sign . number_format($transaction->amount, 2) . ' ج.م</span>';
                })
                ->addColumn('balance_formatted', function ($transaction) {
                    return '<span class="fw-bold">' . number_format($transaction->balance_after, 2) . ' ج.م</span>';
                })
                ->addColumn('created_by_name', function ($transaction) {
                    return $transaction->creator ? $transaction->creator->name : '-';
                })
                ->rawColumns(['type_badge', 'amount_formatted', 'balance_formatted'])
                ->make(true);
        }

        return view('building-fund.index', compact('currentBalance'));
    }

    /**
     * عرض صفحة حساب العمارة للساكن (للقراءة فقط)
     */
    public function residentView(Request $request)
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        // الحصول على الرصيد الحالي
        $currentBalance = BuildingFundTransaction::getCurrentBalance($tenant->id);

        // إحصائيات شاملة
        $totalIncome = BuildingFundTransaction::where('tenant_id', $tenant->id)
            ->where('transaction_type', 'income')
            ->sum('amount');

        $totalExpense = BuildingFundTransaction::where('tenant_id', $tenant->id)
            ->where('transaction_type', 'expense')
            ->sum('amount');

        // تفاصيل المصروفات
        $expenseByType = BuildingFundTransaction::where('tenant_id', $tenant->id)
            ->where('transaction_type', 'expense')
            ->selectRaw('expense_type, SUM(amount) as total')
            ->groupBy('expense_type')
            ->get()
            ->pluck('total', 'expense_type');

        $subscriptionExpenses = $expenseByType['subscription'] ?? 0;
        $maintenanceExpenses = $expenseByType['maintenance'] ?? 0;
        $otherExpenses = $expenseByType['other'] ?? 0;

        // آخر المعاملات
        $recentTransactions = BuildingFundTransaction::with('creator')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        return view('building-fund.resident', compact(
            'currentBalance',
            'totalIncome',
            'totalExpense',
            'subscriptionExpenses',
            'maintenanceExpenses',
            'otherExpenses',
            'recentTransactions'
        ));
    }
}
