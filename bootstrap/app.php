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
	// Process new transactions and create investments automatically
	$schedule->command('app:auto-adjust-real-time-payment-to-investors')
	    ->everyFiveMinutes()
	    ->withoutOverlapping()
	    ->runInBackground()
	    ->appendOutputTo(
	        storage_path('logs/auto-adjust-real-time-payment.log'),
	        true
	    );

	// Sweep USDT balances from user wallets to main wallet
	$schedule->command('crypto:sweep')
	    ->hourly()
	    ->withoutOverlapping()
	    ->runInBackground()
	    ->appendOutputTo(
	        storage_path('logs/crypto-sweep.log'),
	        true
	    );

	// Update earnings based on configured frequency
	$schedule->command('app:update-daily-bonus')
	    ->everyMinute()
	    ->withoutOverlapping()
	    ->runInBackground()
	    ->appendOutputTo(
	        storage_path('logs/update-daily-bonus.log'),
	        true
	    );

	// Process monthly bonuses on 1st of each month
	$schedule->command('app:process-monthly-bonus')
	    ->monthlyOn(1, '00:10')
	    ->withoutOverlapping()
	    ->runInBackground()
	    ->appendOutputTo(
	        storage_path('logs/process-monthly-bonus.log'),
	        true
	    );

	// Distribute referral bonuses to upline chain
	$schedule->command('app:distribute-referral-bonus')
	    ->dailyAt('00:05')
	    ->withoutOverlapping()
	    ->runInBackground()
	    ->appendOutputTo(
	        storage_path('logs/distribute-referral-bonus.log'),
	        true
	    );
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
