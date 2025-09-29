<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ReferralController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Test routes to debug login issues
Route::get('/test-login', function () {
    return 'Login test route works! Available users: ' .
           implode(', ', App\Models\User::pluck('username')->toArray());
});

Route::get('/auth-status', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user' => Auth::check() ? Auth::user()->toArray() : null,
        'session_id' => session()->getId(),
        'session_has_started' => session()->isStarted(),
    ]);
});

// Test route to check if the admin dashboard view can be rendered
Route::get('/test-admin-view', function () {
    try {
        return view('admin.dashboard', [
            'stats' => [
                'total_users' => 0,
                'total_investments' => 0,
                'active_investments' => 0,
                'total_transactions' => 0,
                'pending_withdrawals' => 0,
                'total_wallet_balance' => 0,
                'total_earnings' => 0,
                'total_referral_earnings' => 0,
            ],
            'recentUsers' => [],
            'recentTransactions' => [],
            'topInvestors' => [],
            'monthlyStats' => [],
        ]);
    } catch (\Exception $e) {
        return 'Error rendering view: ' . $e->getMessage();
    }
});

// Debug CSRF token
Route::post('/debug-csrf', function (Request $request) {
    return response()->json([
        'session_token' => csrf_token(),
        'request_token' => $request->input('_token'),
        'request_data' => $request->all(),
        'session_id' => session()->getId(),
        'has_session' => session()->isStarted(),
        'tokens_match' => hash_equals(csrf_token(), $request->input('_token', ''))
    ]);
});

// Handle referral registration
Route::get('/register', function () {
    $referralCode = request('ref');
    return view('auth.register', compact('referralCode'));
})->name('register');

Route::middleware(['auth', 'verified', 'ensure.user.role'])->group(function () {
    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Wallet Management
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])->name('withdraw');
        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
    });

    // Investment Management
    Route::prefix('investments')->name('investments.')->group(function () {
        Route::get('/', [InvestmentController::class, 'index'])->name('index');
        Route::get('/plans', [InvestmentController::class, 'plans'])->name('plans');
        Route::post('/invest', [InvestmentController::class, 'invest'])->name('invest');
        Route::get('/history', [InvestmentController::class, 'history'])->name('history');
    });

    // Referral System
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::get('/tree', [ReferralController::class, 'tree'])->name('tree');
        Route::get('/earnings', [ReferralController::class, 'earnings'])->name('earnings');
    });

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Special Impersonation Routes (accessible during impersonation)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/stop-impersonation', [App\Http\Controllers\Admin\UserManagementController::class, 'stopImpersonation'])->name('stop-impersonation');
});

// Admin Routes
Route::middleware(['auth', 'ensure.admin.role'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard and Core Management
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'userShow'])->name('users.show');
    Route::post('/users/{user}/add-funds', [App\Http\Controllers\Admin\UserManagementController::class, 'addFunds'])->name('users.add-funds');
    Route::post('/users/{user}/deduct-funds', [App\Http\Controllers\Admin\UserManagementController::class, 'deductFunds'])->name('users.deduct-funds');
    Route::post('/users/{user}/ban', [App\Http\Controllers\Admin\UserManagementController::class, 'banUser'])->name('users.ban');
    Route::post('/users/{user}/unban', [App\Http\Controllers\Admin\UserManagementController::class, 'unbanUser'])->name('users.unban');
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\Admin\UserManagementController::class, 'impersonateUser'])->name('users.impersonate');

    // Investment Plans Management
    Route::resource('investment-plans', App\Http\Controllers\Admin\InvestmentPlanController::class);

    // Transaction Management
    Route::get('/transactions', [App\Http\Controllers\Admin\AdminController::class, 'transactions'])->name('transactions.index');
    Route::post('/transactions/{transaction}/approve', [App\Http\Controllers\Admin\UserManagementController::class, 'approveTransaction'])->name('transactions.approve');
    Route::post('/transactions/{transaction}/reject', [App\Http\Controllers\Admin\UserManagementController::class, 'rejectTransaction'])->name('transactions.reject');

    // Investment Management
    Route::get('/investments', [App\Http\Controllers\Admin\AdminController::class, 'investments'])->name('investments.index');
});

require __DIR__.'/auth.php';
