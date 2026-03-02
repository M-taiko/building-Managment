<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\TenantMiddleware::class,
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withSchedule(function ($schedule) {
        // Generate monthly subscriptions on the 1st of each month at 00:01
        $schedule->command('subscriptions:generate-monthly')
            ->monthlyOn(1, '00:01')
            ->timezone('Asia/Riyadh')
            ->description('توليد الاشتراكات الشهرية تلقائياً');

        // Generate recurring expenses daily at 00:05
        $schedule->command('expenses:generate-recurring')
            ->dailyAt('00:05')
            ->timezone('Asia/Riyadh')
            ->description('توليد المصروفات المتكررة تلقائياً');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
