<?php

namespace App\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getUserAnalytics(User $user)
    {
        $wallet = $user->wallet;
        $investments = $user->investments()->where('status', 'active')->get();

        if ($investments->isEmpty()) {
            return null;
        }

        return [
            'wallet' => $this->getWalletAnalytics($wallet),
            'investments' => $this->getInvestmentAnalytics($investments),
            'interest' => $this->getInterestAnalytics($user),
            'weekly' => $this->getWeeklyAnalytics($user),
            'monthly' => $this->getMonthlyAnalytics($user),
            'realtime' => $this->getRealtimeStats($user),
        ];
    }

    protected function getWalletAnalytics($wallet)
    {
        if (!$wallet) {
            return [
                'balance' => 0,
                'invested_amount' => 0,
                'earned_amount' => 0,
                'withdrawn_amount' => 0,
                'referral_earnings' => 0,
                'total_value' => 0,
            ];
        }

        return [
            'balance' => (float) $wallet->balance,
            'invested_amount' => (float) $wallet->invested_amount,
            'earned_amount' => (float) $wallet->earned_amount,
            'withdrawn_amount' => (float) $wallet->withdrawn_amount,
            'referral_earnings' => (float) $wallet->referral_earnings,
            'total_value' => (float) ($wallet->balance + $wallet->invested_amount),
        ];
    }

    protected function getInvestmentAnalytics($investments)
    {
        $totalInvested = $investments->sum('amount');
        $totalExpectedReturn = $investments->sum('expected_return');
        $totalEarned = $investments->sum('earned_amount');
        $activeCount = $investments->count();

        $dailyInterest = $investments->sum(function ($investment) {
            return $investment->calculateDailyEarning();
        });

        return [
            'total_invested' => (float) $totalInvested,
            'total_expected_return' => (float) $totalExpectedReturn,
            'total_earned' => (float) $totalEarned,
            'active_count' => $activeCount,
            'daily_interest' => (float) $dailyInterest,
            'monthly_interest' => (float) ($dailyInterest * 30),
            'yearly_interest' => (float) ($dailyInterest * 365),
            'average_daily_rate' => $totalInvested > 0 ? (float) (($dailyInterest / $totalInvested) * 100) : 0,
        ];
    }

    protected function getInterestAnalytics(User $user)
    {
        $now = Carbon::now();
        
        $todayEarnings = Transaction::where('user_id', $user->id)
            ->where('type', 'interest')
            ->whereDate('created_at', $now->toDateString())
            ->sum('amount');

        $weekEarnings = Transaction::where('user_id', $user->id)
            ->where('type', 'interest')
            ->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
            ->sum('amount');

        $monthEarnings = Transaction::where('user_id', $user->id)
            ->where('type', 'interest')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('amount');

        $allTimeEarnings = Transaction::where('user_id', $user->id)
            ->where('type', 'interest')
            ->sum('amount');

        return [
            'today' => (float) $todayEarnings,
            'this_week' => (float) $weekEarnings,
            'this_month' => (float) $monthEarnings,
            'all_time' => (float) $allTimeEarnings,
        ];
    }

    protected function getWeeklyAnalytics(User $user)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $dailyData = [];
        for ($date = $startOfWeek->copy(); $date <= $endOfWeek; $date->addDay()) {
            $earnings = Transaction::where('user_id', $user->id)
                ->where('type', 'interest')
                ->whereDate('created_at', $date->toDateString())
                ->sum('amount');

            $dailyData[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'earnings' => (float) $earnings,
            ];
        }

        return [
            'period' => 'weekly',
            'start_date' => $startOfWeek->format('Y-m-d'),
            'end_date' => $endOfWeek->format('Y-m-d'),
            'data' => $dailyData,
            'total' => (float) array_sum(array_column($dailyData, 'earnings')),
        ];
    }

    protected function getMonthlyAnalytics(User $user)
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $weeklyData = [];
        $currentWeekStart = $startOfMonth->copy();
        
        while ($currentWeekStart <= $endOfMonth) {
            $currentWeekEnd = $currentWeekStart->copy()->endOfWeek();
            if ($currentWeekEnd > $endOfMonth) {
                $currentWeekEnd = $endOfMonth->copy();
            }

            $earnings = Transaction::where('user_id', $user->id)
                ->where('type', 'interest')
                ->whereBetween('created_at', [$currentWeekStart, $currentWeekEnd])
                ->sum('amount');

            $weeklyData[] = [
                'week_start' => $currentWeekStart->format('Y-m-d'),
                'week_end' => $currentWeekEnd->format('Y-m-d'),
                'week_label' => 'Week ' . $currentWeekStart->weekOfMonth,
                'earnings' => (float) $earnings,
            ];

            $currentWeekStart->addWeek();
        }

        return [
            'period' => 'monthly',
            'month' => $startOfMonth->format('F Y'),
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date' => $endOfMonth->format('Y-m-d'),
            'data' => $weeklyData,
            'total' => (float) array_sum(array_column($weeklyData, 'earnings')),
        ];
    }

    protected function getRealtimeStats(User $user)
    {
        $investments = $user->investments()->where('status', 'active')->get();
        
        $currentValue = $investments->sum(function ($investment) {
            return $investment->amount + $investment->earned_amount;
        });

        $projectedValue = $investments->sum(function ($investment) {
            return $investment->amount + $investment->expected_return;
        });

        $daysActive = $investments->sum(function ($investment) {
            return Carbon::parse($investment->start_date)->diffInDays(Carbon::now());
        });

        $daysRemaining = $investments->sum(function ($investment) {
            $remaining = Carbon::now()->diffInDays(Carbon::parse($investment->end_date), false);
            return max(0, $remaining);
        });

        return [
            'current_value' => (float) $currentValue,
            'projected_value' => (float) $projectedValue,
            'growth_percentage' => $investments->sum('amount') > 0 
                ? (float) ((($currentValue - $investments->sum('amount')) / $investments->sum('amount')) * 100)
                : 0,
            'days_active' => (int) $daysActive,
            'days_remaining' => (int) $daysRemaining,
            'last_updated' => Carbon::now()->toIso8601String(),
        ];
    }

    public function getChartData(User $user, $period = 'week')
    {
        if ($period === 'week') {
            return $this->getWeeklyChartData($user);
        } elseif ($period === 'month') {
            return $this->getMonthlyChartData($user);
        } elseif ($period === 'year') {
            return $this->getYearlyChartData($user);
        }

        return $this->getWeeklyChartData($user);
    }

    protected function getWeeklyChartData(User $user)
    {
        $startDate = Carbon::now()->subDays(6);
        $endDate = Carbon::now();

        $data = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $earnings = Transaction::where('user_id', $user->id)
                ->where('type', 'interest')
                ->whereDate('created_at', $date->toDateString())
                ->sum('amount');

            $data[] = [
                'label' => $date->format('M d'),
                'value' => (float) $earnings,
            ];
        }

        return $data;
    }

    protected function getMonthlyChartData(User $user)
    {
        $startDate = Carbon::now()->subDays(29);
        $endDate = Carbon::now();

        $data = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $earnings = Transaction::where('user_id', $user->id)
                ->where('type', 'interest')
                ->whereDate('created_at', $date->toDateString())
                ->sum('amount');

            $data[] = [
                'label' => $date->format('M d'),
                'value' => (float) $earnings,
            ];
        }

        return $data;
    }

    protected function getYearlyChartData(User $user)
    {
        $startDate = Carbon::now()->startOfYear();
        $endDate = Carbon::now();

        $data = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
            $earnings = Transaction::where('user_id', $user->id)
                ->where('type', 'interest')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');

            $data[] = [
                'label' => $date->format('M Y'),
                'value' => (float) $earnings,
            ];
        }

        return $data;
    }
}
