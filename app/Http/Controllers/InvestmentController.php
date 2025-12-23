<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use App\Models\Transaction;
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

        // Validate investment amount
        if ($amount < $plan->min_amount || $amount > $plan->max_amount) {
            return back()->with('error', "Investment amount must be between $" . number_format($plan->min_amount, 2) . " and $" . number_format($plan->max_amount, 2));
        }

        // If payment method is crypto, create pending investment and redirect to crypto payment page
        if ($paymentMethod === 'crypto') {
            DB::beginTransaction();

            try {
                // Create investment with pending status
                $expectedReturn = ($amount * $plan->total_return_rate / 100);
                $endDate = now()->addDays($plan->duration_days);

                $investment = Investment::create([
                    'user_id' => $user->id,
                    'investment_plan_id' => $plan->id,
                    'investment_plan' => $plan->name,
                    'amount' => $amount,
                    'expected_return' => $expectedReturn,
                    'duration_days' => $plan->duration_days,
                    'daily_return_rate' => $plan->daily_return_rate,
                    'start_date' => now(),
                    'end_date' => $endDate,
                    'status' => 'pending',
                    'payment_method' => 'crypto',
                ]);

                DB::commit();

                // Redirect to crypto payment page where you will handle the crypto payment logic
                return redirect()->route('investments.crypto-payment', $investment->id)
                    ->with('info', 'Please complete the crypto payment to activate your investment.');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Investment creation failed: ' . $e->getMessage());
            }
        }

        // Wallet payment processing
        if (!$user->wallet || $user->wallet->balance < $amount) {
            return back()->with('error', 'Insufficient wallet balance');
        }

        DB::beginTransaction();

        try {
            // Deduct from wallet
            $user->wallet->deductBalance($amount, 'Investment in ' . $plan->name);

            // Create investment
            $expectedReturn = ($amount * $plan->total_return_rate / 100);
            $endDate = now()->addDays($plan->duration_days);

            $investment = Investment::create([
                'user_id' => $user->id,
                'investment_plan_id' => $plan->id,
                'investment_plan' => $plan->name,
                'amount' => $amount,
                'expected_return' => $expectedReturn,
                'duration_days' => $plan->duration_days,
                'daily_return_rate' => $plan->daily_return_rate,
                'start_date' => now(),
                'end_date' => $endDate,
                'status' => 'active',
                'payment_method' => 'wallet',
            ]);

            // Update wallet invested amount
            $user->wallet->increment('invested_amount', $amount);

            // Distribute 5-level referral bonuses
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
                ->with('success', 'Investment created successfully!');

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
        if ($investment->user_id !== $user->id) {
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
}
