<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_investments' => Investment::sum('amount'),
            'active_investments' => Investment::where('status', 'active')->count(),
            'total_transactions' => Transaction::where('status', 'completed')->sum('amount'),
            'pending_withdrawals' => Transaction::where('type', 'withdrawal')->where('status', 'pending')->count(),
            'total_wallet_balance' => Wallet::sum('balance'),
            'total_earnings' => Wallet::sum('earned_amount'),
            'total_referral_earnings' => Wallet::sum('referral_earnings'),
        ];

        $recentUsers = User::with('wallet', 'roles')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentTransactions = Transaction::with('user')->orderBy('created_at', 'desc')->limit(10)->get();
        $topInvestors = User::with('wallet')
            ->join('wallets', 'users.id', '=', 'wallets.user_id')
            ->orderBy('wallets.invested_amount', 'desc')
            ->limit(5)
            ->get();

        $monthlyStats = Transaction::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(amount) as total_amount')
        )
        ->where('created_at', '>=', now()->startOfYear())
        ->where('status', 'completed')
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->orderBy('month')
        ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentTransactions',
            'topInvestors',
            'monthlyStats'
        ));
    }

    public function users()
    {
        $users = User::with(['wallet', 'roles'])
            ->withCount(['referrals', 'investments'])
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function userShow(User $user)
    {
        $user->load(['wallet', 'roles', 'investments', 'transactions', 'referrals']);

        $stats = [
            'total_invested' => $user->investments->sum('amount'),
            'total_earned' => $user->wallet->earned_amount ?? 0,
            'referral_earnings' => $user->wallet->referral_earnings ?? 0,
            'transaction_count' => $user->transactions->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function transactions(Request $request)
    {
        $query = Transaction::with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_search . '%')
                  ->orWhere('email', 'like', '%' . $request->user_search . '%');
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_count' => Transaction::count(),
            'pending_count' => Transaction::where('status', 'pending')->count(),
            'completed_count' => Transaction::where('status', 'completed')->count(),
            'total_volume' => Transaction::where('status', 'completed')->sum('amount'),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats'));
    }

    public function investments()
    {
        $investments = Investment::with('user')->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_count' => Investment::count(),
            'total_amount' => Investment::sum('amount'),
            'active_count' => Investment::where('status', 'active')->count(),
            'completed_count' => Investment::where('status', 'completed')->count(),
        ];

        return view('admin.investments.index', compact('investments', 'stats'));
    }
}
