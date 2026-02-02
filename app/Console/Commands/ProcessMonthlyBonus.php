<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessMonthlyBonus extends Command
{
    protected $signature = 'app:process-monthly-bonus {--force : Force processing even if already processed this month}';

    protected $description = 'Process monthly bonus for active investments based on monthly earnings';

    public function handle()
    {
        $this->info('Starting monthly bonus processing...');

        $lastMonth = now()->subMonth();
        $monthStart = $lastMonth->copy()->startOfMonth();
        $monthEnd = $lastMonth->copy()->endOfMonth();
        $bonusMonth = $lastMonth->format('Y-m');
        $force = $this->option('force');

        $totalBonuses = 0;
        $processedCount = 0;

        try {
            DB::transaction(function () use ($monthStart, $monthEnd, $bonusMonth, $force, &$totalBonuses, &$processedCount) {
                $activeInvestments = Investment::where('status', 'active')
                    ->where('start_date', '<=', $monthEnd)
                    ->whereHas('investmentPlan', function ($q) {
                        $q->where('monthly_bonus_rate', '>', 0);
                    })
                    ->where(function ($q) use ($bonusMonth, $force) {
                        if (!$force) {
                            $q->whereNull('last_monthly_bonus_date')
                              ->orWhere('last_monthly_bonus_date', '<', Carbon::parse($bonusMonth . '-01'));
                        }
                    })
                    ->lockForUpdate()
                    ->get();

                foreach ($activeInvestments as $investment) {
                    $plan = $investment->investmentPlan;
                    if (!$plan || $plan->monthly_bonus_rate <= 0) {
                        continue;
                    }

                    // Calculate total earnings for previous month from transactions
                    $monthEarnings = Transaction::where('user_id', $investment->user_id)
                        ->where('type', 'earning')
                        ->where('status', 'completed')
                        ->where('description', 'like', "%#{$investment->id}")
                        ->whereBetween('created_at', [$monthStart, $monthEnd->copy()->endOfDay()])
                        ->sum('amount');

                    if ($monthEarnings <= 0) {
                        $this->line("Investment #{$investment->id}: no earnings in {$bonusMonth}, skipping");
                        continue;
                    }

                    $bonus = $monthEarnings * ($plan->monthly_bonus_rate / 100);

                    if ($bonus <= 0) {
                        continue;
                    }

                    // Credit wallet
                    $wallet = Wallet::where('user_id', $investment->user_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$wallet) {
                        $this->error("Wallet not found for user {$investment->user_id}");
                        continue;
                    }

                    $wallet->earned_amount += $bonus;
                    $wallet->balance += $bonus;
                    $wallet->save();

                    // Create transaction
                    Transaction::create([
                        'user_id' => $investment->user_id,
                        'transaction_id' => 'MBONUS_' . uniqid() . '_' . date('YmdHis'),
                        'type' => 'monthly_bonus',
                        'amount' => $bonus,
                        'net_amount' => $bonus,
                        'status' => 'completed',
                        'description' => "Monthly bonus ({$plan->monthly_bonus_rate}%) for {$bonusMonth} on investment #{$investment->id}",
                        'processed_at' => now(),
                    ]);

                    // Update investment
                    $investment->monthly_bonus_earned += $bonus;
                    $investment->last_monthly_bonus_date = now()->toDateString();
                    $investment->save();

                    $totalBonuses += $bonus;
                    $processedCount++;

                    $this->line("Investment #{$investment->id}: {$bonus} bonus ({$plan->monthly_bonus_rate}% of {$monthEarnings})");
                }
            });

            $this->info("Monthly bonus processing completed!");
            $this->info("Month: {$bonusMonth}");
            $this->info("Processed: {$processedCount} investments");
            $this->info("Total bonuses: {$totalBonuses}");

            Log::info('Monthly bonus processing completed', [
                'month' => $bonusMonth,
                'processed' => $processedCount,
                'total_bonuses' => $totalBonuses,
            ]);

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Monthly bonus processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}
