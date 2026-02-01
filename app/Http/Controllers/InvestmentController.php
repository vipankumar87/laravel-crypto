<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use App\Models\Transaction;
use App\Models\FeeSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\WalletService;
use App\Services\ReferralService;

class InvestmentController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }
    public function index()
    {
        $investments = Auth::user()->investments()
            ->with('investmentPlan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('investments.index', compact('investments'));
    }

    public function plans()
    {
        $plans = InvestmentPlan::active()->get();
        return view('investments.plans', compact('plans'));
    }

    public function invest(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:investment_plans,id',
            'amount' => 'required|numeric|min:1',
            'source' => 'nullable|string|in:direct,crypto',
            'payment_method' => 'required|string|in:wallet,crypto',
        ]);

        $user = Auth::user();
        $plan = InvestmentPlan::findOrFail($request->plan_id);
        $amount = $request->amount;
        $source = $request->source ?? 'direct';
        $paymentMethod = $request->payment_method;

        // Calculate fees
        $feeBreakdown = FeeSetting::calculateTotalFees($amount);
        $totalFees = $feeBreakdown['total_fees'];
        $netAmount = $feeBreakdown['net_amount'];

        // Validate that net amount is positive
        if ($netAmount <= 0) {
            return back()->with('error', 'Investment amount is too low after fees. Please increase your investment amount.');
        }

        // Validate investment amount (net amount after fees must meet plan requirements)
        if ($netAmount < $plan->min_amount || $netAmount > $plan->max_amount) {
            return back()->with('error', "Net investment amount (after fees) must be between $" . number_format($plan->min_amount, 2) . " and $" . number_format($plan->max_amount, 2) . ". Your net amount would be $" . number_format($netAmount, 2));
        }

        // If payment method is crypto, create pending investment and redirect to crypto payment page
        if ($paymentMethod === 'crypto') {
            DB::beginTransaction();

            try {
                // Create investment with pending status (using net amount after fees)
                $expectedReturn = ($netAmount * $plan->total_return_rate / 100);
                $endDate = now()->addDays($plan->duration_days);

                $investment = Investment::create([
                    'user_id' => $user->id,
                    'investment_plan_id' => $plan->id,
                    'investment_plan' => $plan->name,
                    'amount' => $netAmount, // Net amount after fees
                    'expected_return' => $expectedReturn,
                    'duration_days' => $plan->duration_days,
                    'daily_return_rate' => $plan->daily_return_rate,
                    'start_date' => now(),
                    'end_date' => $endDate,
                    'status' => 'pending',
                    'payment_method' => 'crypto',
                    'platform_fee' => $feeBreakdown['platform_fee'],
                    'transaction_fee' => $feeBreakdown['transaction_fee'],
                    'total_fees' => $totalFees,
                    'gross_amount' => $amount, // Original amount before fees
                ]);

                DB::commit();

                // Redirect to crypto payment page where you will handle the crypto payment logic
                return redirect()->route('investments.crypto-payment', $investment->id)
                    ->with('info', 'Please complete the crypto payment to activate your investment. Total to pay: $' . number_format($amount, 2) . ' (Investment: $' . number_format($netAmount, 2) . ' + Fees: $' . number_format($totalFees, 2) . ')');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Investment creation failed: ' . $e->getMessage());
            }
        }

        // Wallet payment processing - need full amount (including fees)
        if (!$user->wallet || $user->wallet->balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance. You need $' . number_format($amount, 2) . ' (Investment: $' . number_format($netAmount, 2) . ' + Fees: $' . number_format($totalFees, 2) . ')');
        }

        DB::beginTransaction();

        try {
            // Deduct full amount from wallet (including fees)
            $user->wallet->deductBalance($amount, 'Investment in ' . $plan->name . ' ($' . number_format($netAmount, 2) . ' + Fees: $' . number_format($totalFees, 2) . ')', 'investment');

            // Create investment with net amount
            $expectedReturn = ($netAmount * $plan->total_return_rate / 100);
            $endDate = now()->addDays($plan->duration_days);

            $investment = Investment::create([
                'user_id' => $user->id,
                'investment_plan_id' => $plan->id,
                'investment_plan' => $plan->name,
                'amount' => $netAmount, // Net amount after fees
                'expected_return' => $expectedReturn,
                'duration_days' => $plan->duration_days,
                'daily_return_rate' => $plan->daily_return_rate,
                'start_date' => now(),
                'end_date' => $endDate,
                'status' => 'active',
                'payment_method' => 'wallet',
                'platform_fee' => $feeBreakdown['platform_fee'],
                'transaction_fee' => $feeBreakdown['transaction_fee'],
                'total_fees' => $totalFees,
                'gross_amount' => $amount, // Original amount before fees
            ]);

            // Update wallet invested amount (only net amount counts as investment)
            $user->wallet->increment('invested_amount', $netAmount);

            // Distribute 5-level referral bonuses (based on net investment amount)
            if ($user->referred_by) {
                $bonusResult = $this->referralService->distributeInvestmentBonuses($user, $investment);

                if ($bonusResult['success']) {
                    \Log::info('5-level referral bonuses distributed', [
                        'user_id' => $user->id,
                        'investment_id' => $investment->id,
                        'total_distributed' => $bonusResult['total_distributed'],
                        'levels' => count($bonusResult['bonuses_distributed']),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('investments.index')
                ->with('success', 'Investment created successfully! Invested: $' . number_format($netAmount, 2) . ' (Fees: $' . number_format($totalFees, 2) . ')');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Investment failed: ' . $e->getMessage());
        }
    }

    public function cryptoPayment($investmentId)
    {
        $investment = Investment::findOrFail($investmentId);
        $user = Auth::user();

        // Ensure the investment belongs to the authenticated user
        if ((int) $investment->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized access to this investment.');
        }

        // Ensure the investment is in pending status
        if ($investment->status !== 'pending') {
            return redirect()->route('investments.index')
                ->with('error', 'This investment has already been processed.');
        }

        // Check if user has crypto wallet fields, generate if missing
        if (empty($user->crypto_address) || empty($user->private_key)) {
            // try {
                $walletService = new WalletService();
                $wallet = $walletService->generateUserWallet($user->id);

                $user->update([
                    'crypto_address' => $wallet['address'],
                    'private_key' => $walletService->encryptPrivateKey($wallet['private_key']),
                    'public_key' => $wallet['public_key'],
                ]);

                // Refresh the user instance to get updated data
                $user->refresh();

                // Also update the Auth user instance
                Auth::setUser($user);

            // } catch (\Exception $e) {
            //     \Log::error('Failed to generate crypto wallet for user ' . $user->id . ': ' . $e->getMessage());
            //     return back()->with('error', 'Failed to generate crypto wallet. Please try again or contact support.');
            // }
        }

        // Return the crypto payment view where you will code the payment logic
        return view('investments.crypto-payment', compact('investment'));
    }

    public function history()
    {
        $investments = Auth::user()->investments()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('investments.history', compact('investments'));
    }

    public function processPayment(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get count of investments before running command
            $investmentsBefore = \App\Models\Investment::where('user_id', $user->id)
                ->where('status', 'active')
                ->count();
            
            // Run the Laravel command
            \Artisan::call('app:auto-adjust-real-time-payment-to-investors');
            
            // Get the command output
            $output = \Artisan::output();
            
            // Get count of investments after running command
            $investmentsAfter = \App\Models\Investment::where('user_id', $user->id)
                ->where('status', 'active')
                ->count();
            
            // Calculate how many new investments were created
            $newInvestmentsCount = $investmentsAfter - $investmentsBefore;
            
            // Only return success if new investments were created
            if ($newInvestmentsCount > 0) {
                // Get the newly created investments
                $newInvestments = \App\Models\Investment::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->take($newInvestmentsCount)
                    ->get();
                
                // Get the corresponding transactions
                $transactionIds = $newInvestments->pluck('id');
                $newTransactions = \App\Models\UserTransaction::where('user_id', $user->id)
                    ->whereIn('invests_id', $transactionIds)
                    ->get();
                
                // Calculate totals
                $totalAmountFound = $newTransactions->sum('amount');
                $totalDogeInvested = $newInvestments->sum('amount');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully!',
                    'data' => [
                        'amount_found' => number_format($totalAmountFound, 2),
                        'doge_invested' => number_format($totalDogeInvested, 8),
                        'transactions_count' => $newTransactions->count(),
                        'investments_count' => $newInvestmentsCount,
                        'command_output' => $output
                    ]
                ]);
            } else {
                // No new transactions found
                return response()->json([
                    'success' => false,
                    'message' => 'No new transactions found',
                    'data' => [
                        'amount_found' => '0.00',
                        'doge_invested' => '0.00000000',
                        'transactions_count' => 0,
                        'investments_count' => 0,
                        'command_output' => $output
                    ]
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
