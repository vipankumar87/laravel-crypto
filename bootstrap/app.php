<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Process new transactions and create investments automatically
        $schedule->command('app:auto-adjust-real-time-payment-to-investors')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Sweep USDT balances from user wallets to main wallet
        $schedule->command('crypto:sweep')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Update daily bonuses for earning wallets (self and referral earnings)
        $schedule->command('app:update-daily-bonus')
            ->daily()
            ->at('00:01') // Run at 12:01 AM to process the previous day's earnings
            ->withoutOverlapping()
            ->runInBackground();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Register Spatie Permission middleware
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.banned' => \App\Http\Middleware\CheckBannedUser::class,
            'prevent.system.login' => \App\Http\Middleware\PreventSystemUserLogin::class,
            'ensure.user.role' => \App\Http\Middleware\EnsureUserRole::class,
            'ensure.admin.role' => \App\Http\Middleware\EnsureAdminRole::class,
            'ensure.crypto.wallet' => \App\Http\Middleware\EnsureCryptoWallet::class,
        ]);

        // Apply banned user check, prevent system login, and ensure crypto wallet to web routes
        $middleware->web([
            \App\Http\Middleware\CheckBannedUser::class,
            \App\Http\Middleware\PreventSystemUserLogin::class,
            \App\Http\Middleware\EnsureCryptoWallet::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
