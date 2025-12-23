<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index()
    {
        $user = Auth::user();

        // Check if user has invested
        if (!$user->hasInvested()) {
            return view('referrals.dashboard', [
                'walletBalance' => 0,
                'totalInterest' => 0,
                'totalInvestment' => 0,
                'totalAffiliateBonus' => 0,
                'referralUrl' => null,
                'levelStats' => [],
                'hasInvested' => false,
                'message' => 'You need to make an investment before you can access your referral link.'
            ]);
        }

        // Get wallet and investment stats
        $wallet = $user->wallet;
        $walletBalance = $wallet ? $wallet->balance : 0;
        $totalInterest = $wallet ? $wallet->earned_amount : 0;
        $totalInvestment = $wallet ? $wallet->invested_amount : 0;
        $totalAffiliateBonus = $wallet ? $wallet->referral_earnings : 0;

        // Get referral URL
        $referralUrl = $user->getReferralUrl();

        // Get level-by-level statistics (up to 6 levels)
        $levelStats = [];
        for ($level = 1; $level <= 6; $level++) {
            $levelReferrals = $user->getReferralsByLevel($level);
            $teamSize = $levelReferrals->count();
            
            // Calculate total investment for this level
            $totalInvest = 0;
            foreach ($levelReferrals as $referral) {
                if ($referral->wallet) {
                    $totalInvest += $referral->wallet->invested_amount;
                }
            }
            
            // Calculate average bonus for this level
            $avgBonus = \App\Models\ReferralBonus::where('referrer_id', $user->id)
                ->where('level', $level)
                ->where('status', 'completed')
                ->avg('amount') ?? 0;
            
            $levelStats[] = [
                'level' => $level,
                'team_size' => $teamSize,
                'total_invest' => $totalInvest,
                'avg_bonus' => $avgBonus,
            ];
        }

        return view('referrals.dashboard', compact(
            'walletBalance',
            'totalInterest',
            'totalInvestment',
            'totalAffiliateBonus',
            'referralUrl',
            'levelStats'
        ));
    }

    public function levelDetails($level)
    {
        $user = Auth::user();

        if (!$user->hasInvested()) {
            return redirect()->route('referrals.index')
                ->with('error', 'You need to make an investment before you can view referral details.');
        }

        // Get referrals at this specific level
        $referrals = $user->getReferralsByLevel($level);
        
        // Prepare detailed data
        $referralDetails = [];
        foreach ($referrals as $referral) {
            // Find who referred this user (sponsor)
            $sponsor = $referral->referrer ? $referral->referrer->name : 'Direct';
            
            // Calculate monthly income (average of last 30 days interest)
            $monthlyIncome = \App\Models\Transaction::where('user_id', $referral->id)
                ->where('type', 'interest')
                ->where('created_at', '>=', now()->subDays(30))
                ->sum('amount');
            
            $referralDetails[] = [
                'sponsor' => $sponsor,
                'username' => $referral->username,
                'name' => $referral->name,
                'email' => $referral->email,
                'total_invest' => $referral->wallet ? $referral->wallet->invested_amount : 0,
                'monthly_income' => $monthlyIncome,
                'joined_at' => $referral->created_at,
            ];
        }

        return view('referrals.level-details', compact('level', 'referralDetails'));
    }

    public function tree()
    {
        $user = Auth::user();

        if (!$user->hasInvested()) {
            return redirect()->route('referrals.index')->with('error', 'You need to make an investment before you can view your referral tree.');
        }

        $referralTree = $this->buildReferralTree($user, 3); // 3 levels deep

        return view('referrals.tree', compact('referralTree'));
    }

    public function earnings()
    {
        $user = Auth::user();

        if (!$user->hasInvested()) {
            return redirect()->route('referrals.index')->with('error', 'You need to make an investment before you can view your referral earnings.');
        }

        // Get 5-level referral bonuses
        $bonuses = $user->referralBonuses()
            ->with(['user', 'investment'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get statistics
        $referralStats = $this->referralService->getReferralStats($user);
        $totalEarnings = $referralStats['total_earnings'];
        $earningsByLevel = $referralStats['earnings_by_level'];

        // Get level percentages
        $levelPercentages = $this->referralService->getLevelPercentages();

        return view('referrals.earnings', compact('bonuses', 'totalEarnings', 'earningsByLevel', 'levelPercentages'));
    }

    private function buildReferralTree($user, $maxLevels, $currentLevel = 1)
    {
        if ($currentLevel > $maxLevels) {
            return [];
        }

        $tree = [];
        $referrals = $user->referrals()->with('wallet')->get();

        foreach ($referrals as $referral) {
            $node = [
                'user' => $referral,
                'level' => $currentLevel,
                'children' => $this->buildReferralTree($referral, $maxLevels, $currentLevel + 1)
            ];
            $tree[] = $node;
        }

        return $tree;
    }
}
