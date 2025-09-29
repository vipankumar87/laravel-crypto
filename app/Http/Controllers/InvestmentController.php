<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestmentController extends Controller
{
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
        ]);

        $user = Auth::user();
        $plan = InvestmentPlan::findOrFail($request->plan_id);
        $amount = $request->amount;

        // Validate investment amount
        if ($amount < $plan->min_amount || $amount > $plan->max_amount) {
            return back()->with('error', "Investment amount must be between $" . number_format($plan->min_amount, 2) . " and $" . number_format($plan->max_amount, 2));
        }

        // Check wallet balance
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

            Investment::create([
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
            ]);

            // Update wallet invested amount
            $user->wallet->increment('invested_amount', $amount);

            // Pay referral bonus if user has referrer
            if ($user->referred_by) {
                $referrer = $user->referrer;
                if ($referrer && $referrer->wallet) {
                    $bonus = ($amount * $plan->referral_bonus_rate / 100);
                    $referrer->wallet->addBalance($bonus, 'Referral bonus from ' . $user->name);
                    $referrer->wallet->increment('referral_earnings', $bonus);
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

    public function history()
    {
        $investments = Auth::user()->investments()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('investments.history', compact('investments'));
    }
}
