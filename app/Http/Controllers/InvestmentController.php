<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestmentController extends Controller
{
    public function index()
    {
        $investments = Auth::user()->investments()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('investments.index', compact('investments'));
    }

    public function crypto()
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        return view('investments.crypto', compact('user', 'wallet'));
    }

    public function invest(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_currency' => 'required|string|max:10',
            'investment_currency' => 'required|string|max:10',
        ]);

        $user = Auth::user();
        $amount = $request->amount;
        $paymentCurrency = $request->payment_currency;
        $investmentCurrency = $request->investment_currency;

        // Default investment parameters (since no plans)
        $dailyReturnRate = 2.0; // 2% daily return
        $durationDays = 30; // 30 days duration
        $totalReturnRate = $dailyReturnRate * $durationDays; // 60% total return
        $referralBonusRate = 5.0; // 5% referral bonus

        // Processing fee for crypto investments
        $processingFee = 1.0; // 1 USDT processing fee
        $totalDeduction = $amount + $processingFee;

        // Validate minimum investment
        if ($amount < 10) {
            return back()->with('error', 'Minimum investment amount is $10');
        }

        // Check wallet balance
        if (!$user->wallet || $user->wallet->balance < $totalDeduction) {
            return back()->with('error', 'Insufficient wallet balance (including processing fee)');
        }

        DB::beginTransaction();

        try {
            // Deduct from wallet
            $description = "Crypto investment (with processing fee)";
            $deductSuccess = $user->wallet->deductBalance($totalDeduction, $description);

            if (!$deductSuccess) {
                throw new \Exception('Failed to deduct amount from wallet');
            }

            // Calculate returns
            $expectedReturn = ($amount * $totalReturnRate / 100);
            $endDate = now()->addDays($durationDays);

            // Create investment
            Investment::create([
                'user_id' => $user->id,
                'investment_plan_id' => null, // No plan needed
                'investment_plan' => 'Crypto Investment',
                'amount' => $amount,
                'payment_currency' => $paymentCurrency,
                'investment_currency' => $investmentCurrency,
                'payment_amount' => $amount,
                'exchange_rate' => 1.0, // Will be updated with actual rate
                'conversion_fee' => $processingFee,
                'expected_return' => $expectedReturn,
                'duration_days' => $durationDays,
                'daily_return_rate' => $dailyReturnRate,
                'start_date' => now(),
                'end_date' => $endDate,
                'status' => 'active',
            ]);

            // Update wallet invested amount
            $user->wallet->increment('invested_amount', $amount);

            // Pay referral bonus if user has referrer
            if ($user->referred_by) {
                $referrer = $user->referrer;
                if ($referrer && $referrer->wallet) {
                    $bonus = ($amount * $referralBonusRate / 100);
                    $referrer->wallet->addBalance($bonus, 'Referral bonus from ' . $user->username);
                    $referrer->wallet->increment('referral_earnings', $bonus);
                }
            }

            DB::commit();

            return redirect()->route('investments.index')
                ->with('success', 'Crypto investment created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Investment failed: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $investments = Auth::user()->investments()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('investments.history', compact('investments'));
    }
}
