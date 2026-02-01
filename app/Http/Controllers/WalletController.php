<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WithdrawalSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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

        // Check for DOGE bonus eligibility
        $wallet->checkAndAwardDogeBonus();
        $wallet->refresh();

        $withdrawalSettings = [
            'min_usdt_threshold' => WithdrawalSetting::getMinUsdtThreshold(),
            'max_withdrawal_amount' => WithdrawalSetting::getMaxWithdrawalAmount(),
        ];

        $canWithdraw = ($wallet->earned_amount + $wallet->referral_earnings) >= $withdrawalSettings['min_usdt_threshold'];

        $pendingWithdrawals = Transaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->count();

        // Fetch DOGE/USDT rate (cached for 5 minutes)
        $dogeRate = self::getDogeRate();
        $dogeBalanceInUsdt = $dogeRate > 0 ? round($wallet->balance * $dogeRate, 2) : 0;

        return view('wallet.index', compact('wallet', 'withdrawalSettings', 'canWithdraw', 'pendingWithdrawals', 'dogeRate', 'dogeBalanceInUsdt'));
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
            'currency' => 'required|in:USDT,DOGE',
        ]);

        $user = Auth::user();
        $wallet = $user->wallet;
        $amount = $request->amount;
        $currency = $request->currency;
        $maxAmount = WithdrawalSetting::getMaxWithdrawalAmount();

        // Check max withdrawal amount
        if ($amount > $maxAmount) {
            return back()->with('error', "Maximum withdrawal amount is $" . number_format($maxAmount, 2));
        }

        if ($currency === 'USDT') {
            // USDT withdrawal checks
            if (!$wallet || $wallet->balance < $amount) {
                return back()->with('error', 'Insufficient USDT balance');
            }

            $minThreshold = WithdrawalSetting::getMinUsdtThreshold();
            $totalEarnings = $wallet->earned_amount + $wallet->referral_earnings;
            if ($totalEarnings < $minThreshold) {
                return back()->with('error', "You need at least $" . number_format($minThreshold, 2) . " in total earnings before you can withdraw USDT.");
            }

            // Calculate fee
            $fee = WithdrawalSetting::calculateWithdrawalFee($amount);
            $netAmount = $amount - $fee;

            // Determine auto-approve
            $autoApprove = WithdrawalSetting::shouldAutoApprove($amount);
            $status = $autoApprove ? 'completed' : 'pending';

            DB::beginTransaction();
            try {
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'WTH_' . uniqid(),
                    'type' => 'withdrawal',
                    'currency' => 'USDT',
                    'amount' => $amount,
                    'fee' => $fee,
                    'net_amount' => $netAmount,
                    'status' => $status,
                    'description' => 'USDT withdrawal request',
                    'processed_at' => $autoApprove ? now() : null,
                ]);

                if ($autoApprove) {
                    $wallet->update([
                        'balance' => $wallet->balance - $amount,
                        'withdrawn_amount' => $wallet->withdrawn_amount + $amount,
                    ]);
                }

                DB::commit();

                if ($autoApprove) {
                    return back()->with('success', 'Withdrawal of $' . number_format($netAmount, 2) . ' USDT approved automatically.');
                }

                return back()->with('success', 'USDT withdrawal request submitted for admin approval.');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Failed to process withdrawal. Please try again.');
            }

        } else {
            // DOGE → USDT withdrawal: user enters USDT amount, we deduct equivalent DOGE
            $dogeRate = self::getDogeRate();
            if ($dogeRate <= 0) {
                return back()->with('error', 'Unable to fetch DOGE conversion rate. Please try again later.');
            }

            // $amount is the USDT value the user wants to withdraw
            $dogeNeeded = round($amount / $dogeRate, 8);

            if (!$wallet || $wallet->balance < $dogeNeeded) {
                return back()->with('error', 'Insufficient DOGE balance. You need ' . number_format($dogeNeeded, 8) . ' DOGE (you have ' . number_format($wallet->balance, 2) . ')');
            }

            // No fee for DOGE-sourced withdrawals, no min threshold check
            $autoApprove = WithdrawalSetting::shouldAutoApprove($amount);
            $status = $autoApprove ? 'completed' : 'pending';

            DB::beginTransaction();
            try {
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_id' => 'WTH_' . uniqid(),
                    'type' => 'withdrawal',
                    'currency' => 'DOGE',
                    'amount' => $amount, // USDT value to send
                    'fee' => 0,
                    'net_amount' => $amount,
                    'status' => $status,
                    'description' => 'DOGE→USDT withdrawal (' . number_format($dogeNeeded, 8) . ' DOGE @ $' . number_format($dogeRate, 6) . ')',
                    'metadata' => [
                        'doge_deducted' => $dogeNeeded,
                        'doge_rate' => $dogeRate,
                    ],
                    'processed_at' => $autoApprove ? now() : null,
                ]);

                if ($autoApprove) {
                    $wallet->update([
                        'balance' => $wallet->balance - $dogeNeeded,
                        'withdrawn_amount' => $wallet->withdrawn_amount + $amount,
                    ]);
                }

                DB::commit();

                if ($autoApprove) {
                    return back()->with('success', 'Withdrawal of $' . number_format($amount, 2) . ' USDT (' . number_format($dogeNeeded, 8) . ' DOGE) approved automatically.');
                }

                return back()->with('success', 'Withdrawal request for $' . number_format($amount, 2) . ' USDT (' . number_format($dogeNeeded, 8) . ' DOGE) submitted for admin approval.');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Failed to process withdrawal. Please try again.');
            }
        }
    }

    public function transactions()
    {
        $transactions = Auth::user()->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('wallet.transactions', compact('transactions'));
    }

    /**
     * Get current DOGE price in USDT, cached for 5 minutes.
     */
    public static function getDogeRate(): float
    {
        return Cache::remember('doge_usdt_rate', 300, function () {
            try {
                $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                    'ids' => 'dogecoin',
                    'vs_currencies' => 'usd',
                ]);

                if ($response->successful()) {
                    $price = $response->json('dogecoin.usd');
                    if ($price && $price > 0) {
                        return (float) $price;
                    }
                }

                // Fallback: Binance
                $response = Http::timeout(10)->get('https://api.binance.com/api/v3/ticker/price', [
                    'symbol' => 'DOGEUSDT',
                ]);

                if ($response->successful()) {
                    $price = $response->json('price');
                    if ($price && $price > 0) {
                        return (float) $price;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch DOGE rate: ' . $e->getMessage());
            }

            return (float) env('DOGE_USD_FALLBACK_RATE', 0.08);
        });
    }
}
