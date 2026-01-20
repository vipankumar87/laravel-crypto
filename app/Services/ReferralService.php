<?php

namespace App\Services;

use App\Models\User;
use App\Models\Investment;
use App\Models\ReferralBonus;
use App\Models\ReferralLevelSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ReferralService
{
    protected $levelPercentages = [];

    public function __construct()
    {
        $this->loadLevelPercentages();
    }

    /**
     * Load level percentages from database with caching
     */
    protected function loadLevelPercentages()
    {
        $this->levelPercentages = Cache::remember('referral_level_percentages', 3600, function () {
            try {
                return ReferralLevelSetting::getLevelPercentages();
            } catch (\Exception $e) {
                // Fallback to defaults if table doesn't exist yet
                return [
                    1 => 10.0,
                    2 => 5.0,
                    3 => 3.0,
                    4 => 2.0,
                    5 => 1.0,
                ];
            }
        });

        // If empty, use defaults
        if (empty($this->levelPercentages)) {
            $this->levelPercentages = [
                1 => 10.0,
                2 => 5.0,
                3 => 3.0,
                4 => 2.0,
                5 => 1.0,
            ];
        }
    }

    /**
     * Clear the cached level percentages
     */
    public static function clearCache()
    {
        Cache::forget('referral_level_percentages');
    }

    /**
     * Distribute referral bonuses across configured levels when user makes an investment
     */
    public function distributeInvestmentBonuses(User $user, Investment $investment)
    {
        $maxLevel = $this->getMaxLevel();
        $uplineChain = $user->getUplineChain($maxLevel);
        $investmentAmount = $investment->amount;
        $bonusesDistributed = [];

        DB::beginTransaction();

        try {
            foreach ($uplineChain as $level => $referrer) {
                if (!$referrer || !$referrer->wallet) {
                    Log::warning("Referrer at level {$level} has no wallet", [
                        'user_id' => $user->id,
                        'referrer_id' => $referrer ? $referrer->id : null,
                    ]);
                    continue;
                }

                $bonusPercentage = $this->levelPercentages[$level] ?? 0;
                if ($bonusPercentage <= 0) {
                    continue;
                }

                $bonusAmount = ($investmentAmount * $bonusPercentage) / 100;

                // Create referral bonus record
                $referralBonus = ReferralBonus::create([
                    'user_id' => $user->id,
                    'referrer_id' => $referrer->id,
                    'investment_id' => $investment->id,
                    'level' => $level,
                    'amount' => $bonusAmount,
                    'investment_amount' => $investmentAmount,
                    'bonus_percentage' => $bonusPercentage,
                    'type' => 'investment',
                    'status' => 'completed',
                    'description' => "Level {$level} referral bonus from {$user->name}'s investment",
                    'processed_at' => now(),
                ]);

                // Add bonus to referrer's wallet
                $referrer->wallet->addBalance(
                    $bonusAmount,
                    "Level {$level} referral bonus from {$user->name}"
                );

                // Update referral earnings in wallet
                $referrer->wallet->increment('referral_earnings', $bonusAmount);

                $bonusesDistributed[] = [
                    'level' => $level,
                    'referrer' => $referrer->name,
                    'amount' => $bonusAmount,
                    'percentage' => $bonusPercentage,
                ];

                Log::info("Referral bonus distributed", [
                    'user_id' => $user->id,
                    'referrer_id' => $referrer->id,
                    'level' => $level,
                    'amount' => $bonusAmount,
                    'investment_id' => $investment->id,
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'bonuses_distributed' => $bonusesDistributed,
                'total_distributed' => array_sum(array_column($bonusesDistributed, 'amount')),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to distribute referral bonuses", [
                'user_id' => $user->id,
                'investment_id' => $investment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get referral statistics for a user
     */
    public function getReferralStats(User $user)
    {
        $stats = [
            'total_earnings' => $user->getTotalReferralEarnings(),
            'earnings_by_level' => [],
            'referrals_by_level' => [],
            'total_referrals' => 0,
        ];

        // Get earnings by level
        $earningsByLevel = $user->getReferralEarningsByLevel();
        foreach ($earningsByLevel as $earning) {
            $stats['earnings_by_level'][$earning->level] = [
                'total' => (float) $earning->total,
                'count' => $earning->count,
                'percentage' => $this->levelPercentages[$earning->level] ?? 0,
            ];
        }

        // Fill in missing levels with zeros
        $maxLevel = $this->getMaxLevel();
        for ($level = 1; $level <= $maxLevel; $level++) {
            if (!isset($stats['earnings_by_level'][$level])) {
                $stats['earnings_by_level'][$level] = [
                    'total' => 0,
                    'count' => 0,
                    'percentage' => $this->levelPercentages[$level] ?? 0,
                ];
            }
        }

        // Get referral counts by level
        $referralCounts = $user->getReferralCountByLevel($maxLevel);
        foreach ($referralCounts as $level => $count) {
            $stats['referrals_by_level'][$level] = $count;
            $stats['total_referrals'] += $count;
        }

        return $stats;
    }

    /**
     * Get detailed referral tree for a user
     */
    public function getReferralTree(User $user, $maxLevels = null)
    {
        $maxLevels = $maxLevels ?? $this->getMaxLevel();
        $tree = [];

        for ($level = 1; $level <= $maxLevels; $level++) {
            $referrals = $user->getReferralsByLevel($level);
            
            $tree[$level] = $referrals->map(function ($referral) use ($level) {
                return [
                    'id' => $referral->id,
                    'name' => $referral->name,
                    'username' => $referral->username,
                    'email' => $referral->email,
                    'joined_at' => $referral->created_at,
                    'total_invested' => $referral->wallet ? $referral->wallet->invested_amount : 0,
                    'active_investments' => $referral->investments()->where('status', 'active')->count(),
                    'level' => $level,
                ];
            })->toArray();
        }

        return $tree;
    }

    /**
     * Calculate potential earnings for a given investment amount
     */
    public function calculatePotentialEarnings($investmentAmount)
    {
        $potential = [];
        $totalPotential = 0;

        foreach ($this->levelPercentages as $level => $percentage) {
            $amount = ($investmentAmount * $percentage) / 100;
            $potential[$level] = [
                'percentage' => $percentage,
                'amount' => $amount,
            ];
            $totalPotential += $amount;
        }

        return [
            'by_level' => $potential,
            'total' => $totalPotential,
        ];
    }

    /**
     * Get level percentages
     */
    public function getLevelPercentages()
    {
        return $this->levelPercentages;
    }

    /**
     * Get maximum configured level
     */
    public function getMaxLevel()
    {
        if (empty($this->levelPercentages)) {
            return 5;
        }
        return max(array_keys($this->levelPercentages));
    }

    /**
     * Set custom level percentages
     */
    public function setLevelPercentages(array $percentages)
    {
        $this->levelPercentages = $percentages;
    }

    /**
     * Reload level percentages from database
     */
    public function reloadLevelPercentages()
    {
        self::clearCache();
        $this->loadLevelPercentages();
    }

    /**
     * Get recent referral bonuses for a user
     */
    public function getRecentBonuses(User $user, $limit = 10)
    {
        return $user->referralBonuses()
            ->with(['user', 'investment'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get referral bonus summary by period
     */
    public function getBonusSummaryByPeriod(User $user, $period = 'month')
    {
        $query = $user->referralBonuses()->where('status', 'completed');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        return [
            'total' => $query->sum('amount'),
            'count' => $query->count(),
            'by_level' => $query->selectRaw('level, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('level')
                ->orderBy('level')
                ->get()
                ->keyBy('level'),
        ];
    }
}
