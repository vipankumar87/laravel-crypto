<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index()
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        if (!$analytics) {
            return redirect()->route('dashboard')
                ->with('info', 'You need to have active investments to view analytics.');
        }

        return view('analytics.index', compact('analytics'));
    }

    public function getAnalytics()
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    public function getChartData(Request $request)
    {
        $user = Auth::user();
        $period = $request->input('period', 'week');

        $chartData = $this->analyticsService->getChartData($user, $period);

        return response()->json([
            'success' => true,
            'data' => $chartData,
        ]);
    }

    public function getWalletStats()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $wallet->balance,
                'invested_amount' => (float) $wallet->invested_amount,
                'earned_amount' => (float) $wallet->earned_amount,
                'withdrawn_amount' => (float) $wallet->withdrawn_amount,
                'referral_earnings' => (float) $wallet->referral_earnings,
                'total_value' => (float) ($wallet->balance + $wallet->invested_amount),
            ],
        ]);
    }

    public function getInvestmentStats()
    {
        $user = Auth::user();
        $investments = $user->investments()->where('status', 'active')->get();

        if ($investments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active investments found',
            ], 404);
        }

        $totalInvested = $investments->sum('amount');
        $totalExpectedReturn = $investments->sum('expected_return');
        $totalEarned = $investments->sum('earned_amount');
        $activeCount = $investments->count();

        $dailyInterest = $investments->sum(function ($investment) {
            return $investment->calculateDailyEarning();
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_invested' => (float) $totalInvested,
                'total_expected_return' => (float) $totalExpectedReturn,
                'total_earned' => (float) $totalEarned,
                'active_count' => $activeCount,
                'daily_interest' => (float) $dailyInterest,
                'monthly_interest' => (float) ($dailyInterest * 30),
                'yearly_interest' => (float) ($dailyInterest * 365),
            ],
        ]);
    }

    public function getInterestBreakdown()
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getUserAnalytics($user);

        if (!$analytics) {
            return response()->json([
                'success' => false,
                'message' => 'No analytics data available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'interest' => $analytics['interest'],
                'weekly' => $analytics['weekly'],
                'monthly' => $analytics['monthly'],
            ],
        ]);
    }
}
