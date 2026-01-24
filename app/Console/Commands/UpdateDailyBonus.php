<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Investment;
use App\Models\Wallet;
use App\Models\ReferralBonus;
use App\Models\ReferralLevelSetting;
use App\Models\User;
use App\Models\DailyBonusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateDailyBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-daily-bonus {--force : Force processing even if already processed today} {--history : Show recent processing history}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update daily bonuses for earning wallets (self and referral earnings)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily bonus update process...');
        
        // Show history if requested
        if ($this->option('history')) {
            $this->showProcessingHistory();
            return 0;
        }
        
        $today = now()->format('Y-m-d');
        $force = $this->option('force');
        
        // Check if already processed today (unless force option is used)
        if (!$force && DailyBonusLog::hasBeenProcessed($today)) {
            $this->info('Daily bonuses have already been processed today. Use --force to override.');
            $this->info('Use --history to see recent processing history.');
            return 0;
        }
        
        $totalSelfEarnings = 0;
        $totalReferralEarnings = 0;
        $processedInvestments = 0;
        
        try {
            // Use database transaction to ensure atomicity
            DB::transaction(function () use (&$totalSelfEarnings, &$totalReferralEarnings, &$processedInvestments, $today, $force) {
                // Process self (direct) earnings from active investments
                $this->processSelfEarnings($totalSelfEarnings, $processedInvestments);
                
                // Process referral earnings (if any new logic needed)
                $this->processReferralEarnings($totalReferralEarnings);
                
                // Store processing record in database
                $notes = $force ? 'Force processing executed' : null;
                DailyBonusLog::createOrUpdateRecord(
                    $today, 
                    $totalSelfEarnings, 
                    $totalReferralEarnings, 
                    $processedInvestments, 
                    $notes
                );
            });
            
            $this->info('Daily bonus update completed successfully!');
            $this->info("Date: {$today}");
            $this->info("Processed Investments: {$processedInvestments}");
            $this->info("Total Self Earnings: {$totalSelfEarnings}");
            $this->info("Total Referral Earnings: {$totalReferralEarnings}");
            $this->info("Total Earnings: " . ($totalSelfEarnings + $totalReferralEarnings));
            
            Log::info('Daily bonus update completed', [
                'date' => $today,
                'self_earnings' => $totalSelfEarnings,
                'referral_earnings' => $totalReferralEarnings,
                'processed_investments' => $processedInvestments,
                'forced' => $force
            ]);
            
        } catch (\Exception $e) {
            $this->error('Error during daily bonus update: ' . $e->getMessage());
            Log::error('Daily bonus update failed', [
                'date' => $today,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Show recent processing history
     */
    private function showProcessingHistory()
    {
        $this->info('Recent Daily Bonus Processing History:');
        $this->info(str_repeat('-', 80));
        
        $history = DailyBonusLog::getRecentHistory(30);
        
        if ($history->isEmpty()) {
            $this->info('No processing history found.');
            return;
        }
        
        $this->line(sprintf(
            "%-12s %-15s %-15s %-15s %-10s %-20s",
            'Date', 'Self Earnings', 'Referral Earnings', 'Total Earnings', 'Investments', 'Processed At'
        ));
        $this->line(str_repeat('-', 80));
        
        foreach ($history as $record) {
            $this->line(sprintf(
                "%-12s %-15s %-15s %-15s %-10s %-20s",
                $record->process_date->format('Y-m-d'),
                number_format($record->total_self_earnings, 2),
                number_format($record->total_referral_earnings, 2),
                number_format($record->total_earnings, 2),
                $record->processed_investments,
                $record->processed_at->format('Y-m-d H:i:s')
            ));
            
            if ($record->notes) {
                $this->line("  Notes: {$record->notes}");
            }
        }
        
        $this->line(str_repeat('-', 80));
        $this->info("Total records: {$history->count()}");
    }
    
    /**
     * Process self (direct) earnings from active investments
     */
    private function processSelfEarnings(&$totalSelfEarnings, &$processedInvestments)
    {
        $this->info('Processing self (direct) earnings...');
        
        $today = now()->format('Y-m-d');
        
        // Get active investments that haven't been processed today
        // Using a more robust query to prevent race conditions
        $activeInvestments = Investment::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where(function($query) use ($today) {
                $query->whereNull('last_earning_date')
                      ->orWhereDate('last_earning_date', '<', $today);
            })
            ->lockForUpdate() // Prevent race conditions
            ->get();
            
        foreach ($activeInvestments as $investment) {
            // Double-check that this investment hasn't been processed today
            if ($investment->last_earning_date && $investment->last_earning_date->format('Y-m-d') === $today) {
                $this->line("Skipping investment #{$investment->id} - already processed today");
                continue;
            }
            
            $dailyEarning = $investment->calculateDailyEarning();
            
            if ($dailyEarning > 0) {
                // Update investment earned amount and last earning date
                $investment->earned_amount += $dailyEarning;
                $investment->last_earning_date = now();
                $investment->save();
                
                // Update wallet earned amount with optimistic locking
                $wallet = Wallet::where('user_id', $investment->user_id)
                    ->lockForUpdate()
                    ->first();
                    
                if ($wallet) {
                    $wallet->earned_amount += $dailyEarning;
                    $wallet->balance += $dailyEarning;
                    $wallet->save();
                    
                    // Create transaction record for the earning
                    $this->createEarningTransaction($investment->user_id, $dailyEarning, 'daily_return', $investment);
                } else {
                    $this->error("Wallet not found for user {$investment->user_id}");
                    continue;
                }
                
                $totalSelfEarnings += $dailyEarning;
                $processedInvestments++;
                
                $this->line("Processed investment #{$investment->id}: {$dailyEarning} for user {$investment->user_id}");
            } else {
                // Still update last_earning_date to prevent reprocessing zero-earnings investments
                $investment->last_earning_date = now();
                $investment->save();
                
                $this->line("Investment #{$investment->id} has zero earnings for today");
            }
        }
        
        $this->info("Self earnings processed: {$processedInvestments} investments");
    }
    
    /**
     * Process referral earnings (this handles ongoing referral bonuses if needed)
     */
    private function processReferralEarnings(&$totalReferralEarnings)
    {
        $this->info('Processing referral earnings...');
        
        // Note: Referral bonuses are typically processed when new investments are made
        // This method can be used for any ongoing referral earning logic
        
        // For now, we'll just log that referral earnings are handled at investment time
        $this->info('Referral earnings are processed at investment time. No additional processing needed.');
    }
    
    /**
     * Create a transaction record for earnings
     */
    private function createEarningTransaction($userId, $amount, $type, $investment = null)
    {
        $today = now()->format('Y-m-d');
        
        // Check if a transaction already exists for this investment today
        if ($investment) {
            $existingTransaction = \App\Models\Transaction::where('user_id', $userId)
                ->where('type', $type)
                ->where('description', 'like', "%#{$investment->id}")
                ->whereDate('created_at', $today)
                ->first();
                
            if ($existingTransaction) {
                $this->line("Transaction already exists for investment #{$investment->id} today");
                return $existingTransaction;
            }
        }
        
        $transaction = \App\Models\Transaction::create([
            'user_id' => $userId,
            'transaction_id' => 'EARN_' . uniqid() . '_' . date('YmdHis'),
            'type' => $type,
            'amount' => $amount,
            'net_amount' => $amount,
            'status' => 'completed',
            'description' => "Daily earning from investment",
            'processed_at' => now(),
        ]);
        
        // Link to investment if provided
        if ($investment) {
            // You might want to add investment_id to transactions table in future
            // For now, we can store it in description or metadata
            $transaction->description .= " #{$investment->id}";
            $transaction->save();
        }
        
        return $transaction;
    }
}
