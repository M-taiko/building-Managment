<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getBuildingAdminStats()
    {
        $currentYear = date('Y');

        // Total income
        $totalIncome = Subscription::whereYear('created_at', $currentYear)
            ->sum('paid_amount');

        // Total expenses
        $totalExpenses = Expense::whereYear('date', $currentYear)
            ->sum('amount');

        // Current balance
        $currentBalance = $totalIncome - $totalExpenses;

        // Apartments with arrears
        $apartmentsWithArrears = Subscription::select('apartment_id')
            ->where('status', '!=', 'paid')
            ->distinct()
            ->count();

        // Open maintenance requests
        $openMaintenanceRequests = MaintenanceRequest::where('status', 'open')
            ->count();

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'current_balance' => $currentBalance,
            'apartments_with_arrears' => $apartmentsWithArrears,
            'open_maintenance_requests' => $openMaintenanceRequests,
        ];
    }

    public function getMonthlyIncomeExpenses()
    {
        $currentYear = date('Y');

        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            $income = Subscription::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $month)
                ->sum('paid_amount');

            $expenses = Expense::whereYear('date', $currentYear)
                ->whereMonth('date', $month)
                ->sum('amount');

            $monthlyData[] = [
                'month' => $month,
                'income' => (float) $income,
                'expenses' => (float) $expenses,
            ];
        }

        return $monthlyData;
    }

    public function getResidentStats($apartmentId)
    {
        // Total paid
        $totalPaid = Subscription::where('apartment_id', $apartmentId)
            ->sum('paid_amount');

        // Total arrears — only unpaid/partial subscriptions (all years) + unpaid expense shares
        $subscriptionArrears = Subscription::where('apartment_id', $apartmentId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        $expenseArrears = \App\Models\ExpenseShare::where('apartment_id', $apartmentId)
            ->where('paid', false)
            ->selectRaw('SUM(share_amount - paid_amount) as total')
            ->value('total') ?? 0;

        $arrears = $subscriptionArrears + $expenseArrears;

        // Payment history
        $paymentHistory = Subscription::where('apartment_id', $apartmentId)
            ->where('paid_amount', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Maintenance requests
        $maintenanceRequests = MaintenanceRequest::where('apartment_id', $apartmentId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return [
            'total_paid' => $totalPaid,
            'arrears' => $arrears,
            'payment_history' => $paymentHistory,
            'maintenance_requests' => $maintenanceRequests,
        ];
    }

    public function getResidentsWithArrears()
    {
        return Apartment::with(['users' => function($query) {
                $query->where('role', 'resident');
            }])
            ->select('apartments.*')
            ->selectRaw('(SELECT SUM(amount - paid_amount) FROM subscriptions WHERE apartment_id = apartments.id) as total_arrears')
            ->having('total_arrears', '>', 0)
            ->get()
            ->map(function($apartment) {
                return [
                    'apartment_number' => $apartment->number,
                    'floor' => $apartment->floor,
                    'owner_name' => $apartment->owner_name,
                    'resident_name' => $apartment->users->first()?->name ?? 'لا يوجد مقيم',
                    'resident_email' => $apartment->users->first()?->email ?? '-',
                    'resident_phone' => $apartment->users->first()?->phone ?? '-',
                    'total_arrears' => $apartment->total_arrears,
                ];
            });
    }

    public function getResidentArrearsDetails($apartmentId)
    {
        return Subscription::where('apartment_id', $apartmentId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($subscription) {
                return [
                    'month'      => $subscription->month,
                    'year'       => $subscription->year,
                    'month_name' => $this->getArabicMonthName($subscription->month),
                    'amount'     => $subscription->amount,
                    'paid_amount'=> $subscription->paid_amount,
                    'remaining'  => $subscription->amount - $subscription->paid_amount,
                    'status'     => $subscription->status,
                ];
            });
    }

    public function getCompletedMaintenance($apartmentId)
    {
        return MaintenanceRequest::where('apartment_id', $apartmentId)
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getPlannedMaintenance($apartmentId)
    {
        return MaintenanceRequest::where('apartment_id', $apartmentId)
            ->whereIn('status', ['open', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getSuperAdminStats()
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::whereHas('users')->count();

        $currentYear = date('Y');
        $currentMonth = date('m');

        // Calculate monthly revenue (subscription_price * number of tenants)
        $monthlyRevenue = Tenant::sum('subscription_price');

        // Calculate yearly revenue
        $yearlyRevenue = $monthlyRevenue * 12;

        // Get tenants list with subscription info
        $tenants = Tenant::withCount([
            'apartments',
            'users' => function($query) {
                $query->where('role', 'resident');
            }
        ])->get();

        return [
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'monthly_revenue' => $monthlyRevenue,
            'yearly_revenue' => $yearlyRevenue,
            'tenants' => $tenants,
        ];
    }

    private function getArabicMonthName($month)
    {
        $months = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        return $months[$month] ?? '';
    }
}
