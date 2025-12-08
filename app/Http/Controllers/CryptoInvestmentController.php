<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CryptoInvestmentController extends Controller
{
    public function index()
    {
        return view('investments.crypto-invest');
    }
    
    public function process(Request $request)
    {
        $request->validate([
            'investment_code' => 'required|string|min:6',
            'amount' => 'required|numeric|min:10',
        ]);
        
        $user = Auth::user();
        $amount = $request->amount;
        $investmentCode = $request->investment_code;
        
        // Default investment parameters
        $dailyReturnRate = 2.0; // 2% daily return
        $durationDays = 30; // 30 days duration
        $totalReturnRate = $dailyReturnRate * $durationDays; // 60% total return
        $referralBonusRate = 5.0; // 5% referral bonus
        $processingFee = 1.0; // $1 processing fee
        $totalAmount = $amount + $processingFee;
        
        DB::beginTransaction();
        
        try {
            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_id' => 'CRYPTO_' . strtoupper(substr($investmentCode, 0, 8)) . '_' . uniqid(),
                'type' => 'deposit',
                'amount' => $totalAmount,
                'net_amount' => $amount,
                'fee' => $processingFee,
                'status' => 'completed',
                'description' => 'Crypto investment deposit',
                'payment_method' => 'crypto',
                'payment_details' => json_encode([
                    'investment_code' => $investmentCode,
                    'crypto_amount' => $amount,
                    'processing_fee' => $processingFee
                ]),
            ]);
            
            // Calculate returns
            $expectedReturn = ($amount * $totalReturnRate / 100);
            $endDate = now()->addDays($durationDays);
            
            // Create investment
            Investment::create([
                'user_id' => $user->id,
                'investment_plan_id' => null,
                'investment_plan' => 'Crypto Investment',
                'amount' => $amount,
                'payment_currency' => 'USDT',
                'investment_currency' => 'USDT',
                'payment_amount' => $totalAmount,
                'exchange_rate' => 1.0,
                'conversion_fee' => $processingFee,
                'expected_return' => $expectedReturn,
                'duration_days' => $durationDays,
                'daily_return_rate' => $dailyReturnRate,
                'start_date' => now(),
                'end_date' => $endDate,
                'status' => 'active',
                'transaction_id' => $transaction->id,
            ]);
            
            // Add to wallet balance for tracking
            if ($user->wallet) {
                $user->wallet->increment('invested_amount', $amount);
            }
            
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
            
            return redirect()->route('dashboard')->with('success', 'Crypto investment processed successfully! Amount: $' . number_format($amount, 2));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Investment failed: ' . $e->getMessage());
        }
    }
}
