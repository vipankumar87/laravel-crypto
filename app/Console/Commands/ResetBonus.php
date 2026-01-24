<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Investment;
use App\Models\Wallet;
use App\Models\ReferralBonus;
use App\Models\User;
use App\Models\DailyBonusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ResetBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-bonus {--user= : Process only for specific user ID} {--from= : Start date (Y-m-d) to check from} {--to= : End date (Y-m-d) to check to (default: today)} {--dry-run : Show what would be done without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset and create missing bonus transactions for active investments from activation day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting reset bonus process...');
        
        $targetUserId = $this->option('user');
        $fromDate = $this->option('from') ? Carbon::createFromFormat('Y-m-d', $this->option('from')) : null;
        $toDate = $this->option('to') ? Carbon::createFromFormat('Y-m-d', $this->option('to')) : now();
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $totalInvestments = 0;
        $totalDaysProcessed = 0;
        $totalEarningsCreated = 0;
        
        try {
            DB::transaction(function () use (&$totalInvestments, &$totalDaysProcessed, &$totalEarningsCreated, $targetUserId, $fromDate, $toDate, $isDryRun) {
                $query = Investment::where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
                
                if ($targetUserId) {
                    $query->where('user_id', $targetUserId);
                }
                
                $investments = $query->get();
                
                foreach ($investments as $investment) {
                    $this->processInvestment($investment, $fromDate, $toDate, $isDryRun, $totalInvestments, $totalDaysProcessed, $totalEarningsCreated);
                }
            });
            
            $this->info('Reset bonus process completed!');
            $this->info("Total Investments Scanned: {$totalInvestments}");
            $this->info("Total Days Processed: {$totalDaysProcessed}");
            $this->info("Total Earnings Created: {$totalEarningsCreated}");
            
            if ($isDryRun) {
                $this->warn('This was a dry run. No actual changes were made.');
            }
            
        } catch (\Exception $e) {
            $this->error('Error during reset bonus: ' . $e->getMessage());
            Log::error('Reset bonus failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Process a single investment for missing days
     */
    private function processInvestment($investment, $fromDate, $toDate, $isDryRun, &$totalInvestments, &$totalDaysProcessed, &$totalEarningsCreated)
    {
        $this->line("Processing investment #{$investment->id} for user {$investment->user_id}");
        $totalInvestments++;
        
        $startDate = $fromDate ? max($fromDate, $investment->start_date) : $investment->start_date;
        $endDate = $toDate ? min($toDate, now()) : now();
        
        // Ensure we don't go beyond investment end date
        $endDate = min($endDate, $investment->end_date);
        
        if ($startDate->greaterThan($endDate)) {
            $this->line("  Skipping - date range is invalid");
            return;
        }
        
        $period = CarbonPeriod::create($startDate, $endDate);
        $daysProcessed = 0;
        $earningsCreated = 0;
        
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            
            // Skip future dates
            if ($date->greaterThan(now())) {
                continue;
            }
            
            // Check if earning transaction already exists for this date
            $existingTx = \App\Models\Transaction::where('user_id', $investment->user_id)
                ->where('type', 'earning')
                ->where('description', 'like', "%#{$investment->id}")
                ->whereDate('created_at', $dateStr)
                ->first();
            
            if ($existingTx) {
                $this->line("  {$dateStr}: Already exists - skipping");
                continue;
            }
            
            // Calculate daily earning for this date
            $dailyEarning = $investment->calculateDailyEarning();
            
            if ($dailyEarning <= 0) {
                $this->line("  {$dateStr}: Zero earning - skipping");
                continue;
            }
            
            // In dry run mode, just show what would be done
            if ($isDryRun) {
                $this->line("  {$dateStr}: Would create earning transaction for {$dailyEarning}");
                $daysProcessed++;
                $earningsCreated += $dailyEarning;
                continue;
            }
            
            // Create the missing earning transaction
            $this->createMissingEarningTransaction($investment, $dailyEarning, $date);
            
            // Update wallet
            $wallet = Wallet::where('user_id', $investment->user_id)->lockForUpdate()->first();
            if ($wallet) {
                $wallet->balance += $dailyEarning;
                $wallet->earned_amount += $dailyEarning;
                $wallet->save();
            }
            
            // Update investment
            $investment->earned_amount += $dailyEarning;
            if (!$investment->last_earning_date || $investment->last_earning_date->lt($date)) {
                $investment->last_earning_date = $date;
            }
            $investment->save();
            
            $this->line("  {$dateStr}: Created earning transaction for {$dailyEarning}");
            $daysProcessed++;
            $earningsCreated += $dailyEarning;
        }
        
        $totalDaysProcessed += $daysProcessed;
        $totalEarningsCreated += $earningsCreated;
        
        $this->line("  Investment #{$investment->id}: {$daysProcessed} days processed, {$earningsCreated} earnings created");
    }
    
    /**
     * Create a missing earning transaction for a specific date
     */
    private function createMissingEarningTransaction($investment, $amount, $date)
    {
        $transaction = \App\Models\Transaction::create([
            'user_id'        => $investment->user_id,
            'transaction_id' => 'RESET_' . uniqid() . '_' . $date->format('YmdHis'),
            'type'           => 'earning',
            'amount'         => $amount,
            'net_amount'     => $amount,
            'status'         => 'completed',
            'description'    => "Reset earning from investment #{$investment->id}",
            'processed_at'   => $date,
            'created_at'     => $date,
            'updated_at'     => $date,
        ]);
        
        return $transaction;
    }
}
