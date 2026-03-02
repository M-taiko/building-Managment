<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Dashboard API Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/recent-payments', [DashboardController::class, 'getRecentPayments']);
        Route::get('/recent-maintenance', [DashboardController::class, 'getRecentMaintenance']);
        Route::get('/chart-data', [DashboardController::class, 'getChartData']);
    });
});
