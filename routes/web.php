<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BuildingFundController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\MonthlyDueController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionTypeController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ApartmentAccountController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard API Routes
    Route::prefix('api/dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/recent-payments', [DashboardController::class, 'getRecentPayments']);
        Route::get('/recent-maintenance', [DashboardController::class, 'getRecentMaintenance']);
        Route::get('/chart-data', [DashboardController::class, 'getChartData']);
    });

    // For Super Admin only - إدارة العمارات
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('tenants', TenantController::class);
    });

    // For Building Admin - إدارة المستخدمين والمقيمين
    Route::middleware('role:building_admin')->group(function () {
        Route::resource('users', UserManagementController::class);
    });

    // Apartments - Only for admins
    Route::middleware('role:building_admin,super_admin')->group(function () {
        Route::resource('apartments', ApartmentController::class);

        // Subscription Types
        Route::resource('subscription-types', SubscriptionTypeController::class);

        // Subscriptions
        Route::resource('subscriptions', SubscriptionController::class);
        Route::get('/subscription-payments', [SubscriptionController::class, 'payments'])->name('subscriptions.payments');
        Route::post('/subscriptions/{id}/mark-paid', [SubscriptionController::class, 'markPaid'])->name('subscriptions.mark-paid');
        Route::post('/subscriptions/send-late-payment-reminders', [SubscriptionController::class, 'sendLatePaymentReminders'])->name('subscriptions.send-late-reminders');
        Route::get('/apartments/{apartmentId}/subscriptions', [SubscriptionController::class, 'getApartmentSubscriptions'])->name('apartments.subscriptions');

        // Expenses
        Route::resource('expenses', ExpenseController::class);
        Route::get('/expenses/{expenseId}/shares', [ExpenseController::class, 'shares'])->name('expenses.shares');
        Route::post('/expense-shares/{shareId}/mark-paid', [ExpenseController::class, 'markSharePaid'])->name('expense-shares.mark-paid');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/history', [PaymentController::class, 'history'])->name('payments.history');
        Route::delete('/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        // Monthly Dues - إدارة المستحقات الشهرية
        Route::get('/monthly-dues', [MonthlyDueController::class, 'index'])->name('monthly-dues.index');
        Route::get('/monthly-dues/settings', [MonthlyDueController::class, 'settings'])->name('monthly-dues.settings');
        Route::post('/monthly-dues/update-amount', [MonthlyDueController::class, 'updateMonthlyAmount'])->name('monthly-dues.update-amount');
        Route::post('/monthly-dues/generate', [MonthlyDueController::class, 'generate'])->name('monthly-dues.generate');
        Route::post('/monthly-dues/bulk-generate', [MonthlyDueController::class, 'bulkGenerate'])->name('monthly-dues.bulk-generate');
        Route::post('/monthly-dues/{id}/pay', [MonthlyDueController::class, 'pay'])->name('monthly-dues.pay');

        // Building Fund - حساب العمارة
        Route::get('/building-fund', [BuildingFundController::class, 'index'])->name('building-fund.index');
        Route::post('/building-fund/transfer', [BuildingFundController::class, 'transfer'])->name('building-fund.transfer');

        // Apartment Account / Wallet
        Route::get('/apartments/{id}/account', [ApartmentAccountController::class, 'show'])->name('apartments.account');
        Route::post('/apartments/{id}/account/deposit', [ApartmentAccountController::class, 'deposit'])->name('apartments.account.deposit');
    });

    // Maintenance Requests - Available for all users
    Route::resource('maintenance', MaintenanceRequestController::class);

    // Notifications - For residents
    Route::middleware('role:resident')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');

        // Building Fund - عرض حساب العمارة للساكن (للقراءة فقط)
        Route::get('/building-fund-view', [BuildingFundController::class, 'residentView'])->name('building-fund.resident');

        // Resident detail pages
        Route::get('/my-payments', [DashboardController::class, 'myPayments'])->name('resident.payments');
        Route::get('/my-arrears', [DashboardController::class, 'myArrears'])->name('resident.arrears');
        Route::get('/my-maintenance', [DashboardController::class, 'myMaintenance'])->name('resident.maintenance');
        Route::post('/my-maintenance', [DashboardController::class, 'storeMaintenance'])->name('resident.maintenance.store');

        // My Wallet
        Route::get('/my-account', [ApartmentAccountController::class, 'myAccount'])->name('resident.account');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

require __DIR__.'/auth.php';
