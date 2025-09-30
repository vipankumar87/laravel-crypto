<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Check if user has invested
        if (!$user->hasInvested()) {
            return view('referrals.index', [
                'referrals' => collect(),
                'stats' => [
                    'total_referrals' => 0,
                    'total_earnings' => 0,
                    'referral_url' => null,
                ],
                'hasInvested' => false,
                'message' => 'You need to make an investment before you can access your referral link.'
            ]);
        }

        $referrals = $user->referrals()->with('wallet')->paginate(10);

        $stats = [
            'total_referrals' => $user->referrals()->count(),
            'total_earnings' => $user->wallet->referral_earnings ?? 0,
            'referral_url' => $user->getReferralUrl(),
        ];

        return view('referrals.index', compact('referrals', 'stats'));
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

        // Get referral earnings from transactions
        $earnings = $user->transactions()
            ->where('type', 'referral_bonus')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalEarnings = $user->wallet->referral_earnings ?? 0;

        return view('referrals.earnings', compact('earnings', 'totalEarnings'));
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
