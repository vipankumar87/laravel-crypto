<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ensure user has a wallet
        try {
            if (!$user->wallet) {
                Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'total_invested' => 0,
                    'total_earned' => 0,
                    'withdrawn_amount' => 0,
                ]);
                $user->refresh();
            }
        } catch (\Exception $e) {
            \Log::error('Error creating wallet', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        // Generate referral code if doesn't exist
        if (!$user->referral_code) {
            $user->generateReferralCode();
        }

        $wallet = $user->wallet;
        $activeInvestments = $user->investments()->where('status', 'active')->count();
        $totalEarnings = $wallet->total_earnings;
        $referralCount = $user->referrals()->count();

        $recentTransactions = $user->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'user',
            'wallet',
            'activeInvestments',
            'totalEarnings',
            'referralCount',
            'recentTransactions'
        ));
    }

    public function adminDashboard()
    {
        die('sdfas');
        $totalUsers = User::count();
        $totalInvestments = Investment::sum('amount');
        $activeInvestments = Investment::where('status', 'active')->count();
        $totalTransactions = Transaction::where('status', 'completed')->sum('amount');

        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentTransactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalInvestments',
            'activeInvestments',
            'totalTransactions',
            'recentUsers',
            'recentTransactions'
        ));
    }

    public function users()
    {
        $users = User::with(['wallet', 'roles'])->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function transactions()
    {
        $transactions = Transaction::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.transactions', compact('transactions'));
    }

    public function investments()
    {
        $investments = Investment::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.investments', compact('investments'));
    }
}
