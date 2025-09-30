<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Investment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ensure user has a wallet
        if (!$user->wallet) {
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);
            $user->refresh();
        }

        $wallet = $user->wallet;

        return view('wallet.index', compact('wallet'));
    }

    public function deposit(Request $request)
    {
        // Only admins can deposit funds directly
        if (!auth()->user()->hasRole(['admin', 'system'])) {
            return back()->with('error', 'Only administrators can add funds. Please contact admin for deposits.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        // This is a placeholder - in real app, integrate payment gateway
        return back()->with('success', 'Deposit request submitted. Please complete payment via your chosen method.');
    }

    public function withdraw(Request $request)
    {
        // Admins and system users cannot withdraw - they manage other users' wallets
        if (auth()->user()->hasRole(['admin', 'system'])) {
            return back()->with('error', 'Administrators cannot make withdrawal requests. Use user management to handle withdrawals.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet || $wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient balance');
        }

        // Create pending withdrawal transaction
        Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => 'WTH_' . uniqid(),
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'net_amount' => $request->amount,
            'status' => 'pending',
            'description' => 'Withdrawal request',
        ]);

        return back()->with('success', 'Withdrawal request submitted for admin approval.');
    }

    public function invest(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
        ]);

        $user = Auth::user();
        $amount = $request->amount;

        // Default investment parameters
        $dailyReturnRate = 2.0; // 2% daily return
        $durationDays = 30; // 30 days duration
        $totalReturnRate = $dailyReturnRate * $durationDays; // 60% total return
        $referralBonusRate = 5.0; // 5% referral bonus
        $processingFee = 1.0; // $1 processing fee
        $totalDeduction = $amount + $processingFee;

        // Check wallet balance
        if (!$user->wallet || $user->wallet->balance < $totalDeduction) {
            return back()->with('error', 'Insufficient wallet balance (including $1 processing fee)');
        }

        DB::beginTransaction();

        try {
            // Deduct from wallet
            $description = "Wallet investment (with $1 processing fee)";
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
                'investment_plan_id' => null,
                'investment_plan' => 'Wallet Investment',
                'amount' => $amount,
                'payment_currency' => 'USDT',
                'investment_currency' => 'USDT',
                'payment_amount' => $amount,
                'exchange_rate' => 1.0,
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

            return back()->with('success', 'Investment created successfully! Amount: $' . number_format($amount, 2));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Investment failed: ' . $e->getMessage());
        }
    }

    public function transactions()
    {
        $transactions = Auth::user()->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('wallet.transactions', compact('transactions'));
    }
}
