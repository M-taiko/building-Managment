<?php

namespace App\Http\Controllers;

use App\Models\ApartmentAccountTransaction;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'super_admin') {
            return $this->superAdminDashboard();
        }

        if ($user->role === 'building_admin') {
            return $this->buildingAdminDashboard();
        }

        return $this->residentDashboard();
    }

    protected function superAdminDashboard()
    {
        $stats = $this->dashboardService->getSuperAdminStats();

        return view('dashboard.super-admin', compact('stats'));
    }

    protected function buildingAdminDashboard()
    {
        $stats = $this->dashboardService->getBuildingAdminStats();
        $monthlyData = $this->dashboardService->getMonthlyIncomeExpenses();
        $residentsWithArrears = $this->dashboardService->getResidentsWithArrears();

        return view('dashboard.building-admin', compact('stats', 'monthlyData', 'residentsWithArrears'));
    }

    protected function residentDashboard()
    {
        $user = auth()->user();
        $stats = $this->dashboardService->getResidentStats($user->apartment_id);
        $arrearsDetails = $this->dashboardService->getResidentArrearsDetails($user->apartment_id);
        $completedMaintenance = $this->dashboardService->getCompletedMaintenance($user->apartment_id);
        $plannedMaintenance = $this->dashboardService->getPlannedMaintenance($user->apartment_id);

        // Get current month data
        $year = request()->input('year', now()->year);
        $month = request()->input('month', now()->month);

        // Monthly Due for this month
        $monthlyDue = \App\Models\MonthlyDue::where('apartment_id', $user->apartment_id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        // Building fund expenses for this month
        $buildingExpenses = \App\Models\BuildingFundTransaction::where('tenant_id', $user->tenant_id)
            ->where('transaction_type', 'expense')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        // Building fund balance
        $buildingBalance = \App\Models\BuildingFundTransaction::getCurrentBalance($user->tenant_id);

        // Apartment wallet balance
        $myAccountBalance = \App\Models\ApartmentAccountTransaction::getCurrentBalance($user->apartment_id);

        // Payment details (subscriptions paid with date)
        $paymentDetails = \App\Models\Subscription::where('apartment_id', $user->apartment_id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->with('subscriptionType')
            ->orderBy('paid_at', 'desc')
            ->get();

        // Expense shares for this resident
        $myExpenseShares = \App\Models\ExpenseShare::where('apartment_id', $user->apartment_id)
            ->with(['expense'])
            ->orderBy('created_at', 'desc')
            ->get();

        // All maintenance requests for this resident
        $myMaintenanceRequests = \App\Models\MaintenanceRequest::where('apartment_id', $user->apartment_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.resident', compact(
            'stats',
            'arrearsDetails',
            'completedMaintenance',
            'plannedMaintenance',
            'monthlyDue',
            'buildingExpenses',
            'buildingBalance',
            'myAccountBalance',
            'year',
            'month',
            'paymentDetails',
            'myExpenseShares',
            'myMaintenanceRequests'
        ));
    }

    public function myPayments()
    {
        $user = auth()->user();

        $paymentDetails = \App\Models\Subscription::where('apartment_id', $user->apartment_id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->with('subscriptionType')
            ->orderBy('paid_at', 'desc')
            ->get();

        $paidExpenseShares = \App\Models\ExpenseShare::where('apartment_id', $user->apartment_id)
            ->where('paid', true)
            ->with('expense')
            ->orderBy('paid_at', 'desc')
            ->get();

        $totalPaid = $paymentDetails->sum('paid_amount') + $paidExpenseShares->sum('share_amount');

        return view('dashboard.my-payments', compact('paymentDetails', 'paidExpenseShares', 'totalPaid'));
    }

    public function myArrears()
    {
        $user = auth()->user();

        $arrearsDetails = $this->dashboardService->getResidentArrearsDetails($user->apartment_id);

        $unpaidExpenseShares = \App\Models\ExpenseShare::where('apartment_id', $user->apartment_id)
            ->where('paid', false)
            ->with('expense')
            ->orderBy('created_at', 'desc')
            ->get();

        $expenseSharesRemaining = $unpaidExpenseShares->sum('share_amount') - $unpaidExpenseShares->sum('paid_amount');
        $totalArrears = $arrearsDetails->sum('remaining') + $expenseSharesRemaining;

        return view('dashboard.my-arrears', compact('arrearsDetails', 'unpaidExpenseShares', 'totalArrears'));
    }

    public function myMaintenance()
    {
        $user = auth()->user();

        $requests = \App\Models\MaintenanceRequest::where('apartment_id', $user->apartment_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.my-maintenance', compact('requests'));
    }

    public function storeMaintenance(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high,urgent',
        ]);

        $validated['apartment_id'] = $user->apartment_id;
        $validated['tenant_id']    = $user->tenant_id;
        $validated['status']       = 'pending';
        $validated['created_by']   = $user->id;

        \App\Models\MaintenanceRequest::create($validated);

        return response()->json(['success' => true, 'message' => 'تم إرسال طلب الصيانة بنجاح']);
    }

    // API Methods
    public function getStats()
    {
        $user = auth()->user();

        if ($user->role === 'building_admin') {
            $stats = $this->dashboardService->getBuildingAdminStats();

            return response()->json([
                'total_income' => number_format($stats['total_income'] ?? 0, 2),
                'total_expenses' => number_format($stats['total_expenses'] ?? 0, 2),
                'balance' => number_format($stats['balance'] ?? 0, 2),
                'apartments_arrears' => $stats['apartments_arrears'] ?? 0,
                'open_maintenance' => $stats['open_maintenance'] ?? 0,
                'total_apartments' => $stats['total_apartments'] ?? 0,
            ]);
        }

        return response()->json([]);
    }

    public function getRecentPayments()
    {
        $user = auth()->user();
        $payments = \App\Models\Subscription::with('apartment')
            ->where('tenant_id', $user->tenant_id)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->orderBy('paid_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json($payments->map(function($payment) {
            return [
                'apartment_number' => $payment->apartment->number ?? '-',
                'amount' => number_format($payment->amount, 2),
                'month' => $payment->month . '/' . $payment->year,
                'date' => $payment->paid_at ? $payment->paid_at->format('Y-m-d') : '-',
            ];
        }));
    }

    public function getRecentMaintenance()
    {
        $user = auth()->user();
        $requests = \App\Models\MaintenanceRequest::with('apartment')
            ->where('tenant_id', $user->tenant_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json($requests->map(function($request) {
            $priorityMap = [
                'high' => 'عالية',
                'medium' => 'متوسطة',
                'low' => 'منخفضة'
            ];

            $statusMap = [
                'pending' => 'معلق',
                'in_progress' => 'قيد التنفيذ',
                'completed' => 'مكتمل'
            ];

            return [
                'apartment_number' => $request->apartment->number ?? '-',
                'title' => $request->title,
                'priority' => $priorityMap[$request->priority] ?? $request->priority,
                'status' => $statusMap[$request->status] ?? $request->status,
            ];
        }));
    }

    public function getChartData(Request $request)
    {
        $user = auth()->user();
        $year = $request->input('year', now()->year);

        $income = [];
        $expenses = [];

        for ($month = 1; $month <= 12; $month++) {
            // Income from subscriptions
            $monthIncome = \App\Models\Subscription::where('tenant_id', $user->tenant_id)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'paid')
                ->sum('amount');

            // Expenses
            $monthExpenses = \App\Models\Expense::where('tenant_id', $user->tenant_id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $income[] = (float) $monthIncome;
            $expenses[] = (float) $monthExpenses;
        }

        return response()->json([
            'income' => $income,
            'expenses' => $expenses,
        ]);
    }
}
