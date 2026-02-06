<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Investment;
use App\Models\Wallet;
use App\Models\WithdrawalSetting;
use App\Models\ReferralBonus;
use App\Models\ReferralLevelSetting;
use App\Models\User;
use App\Models\DailyBonusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        $this->info('Starting earning update process...');

        // Show history if requested
        if ($this->option('history')) {
            $this->showProcessingHistory();
            return 0;
        }

        $today = now()->format('Y-m-d');
        $force = $this->option('force');

        // Get earning frequency settings
        $intervalMinutes = WithdrawalSetting::getIntervalMinutes();
        $intervalsPerDay = WithdrawalSetting::getIntervalsPerDay();

        // Check if enough time has passed since last run (unless force)
        // COMMENTED OUT: This check causes timing edge cases when cron runs at midnight
        // if (!$force) {
        //     $lastLog = DailyBonusLog::where('process_date', $today)->orderBy('processed_at', 'desc')->first();
        //     if ($lastLog && $lastLog->processed_at) {
        //         $minutesSinceLast = Carbon::parse($lastLog->processed_at)->diffInMinutes(now());
        //         if ($minutesSinceLast < $intervalMinutes) {
        //             $this->info("Not enough time passed since last run ({$minutesSinceLast}m < {$intervalMinutes}m). Use --force to override.");
        //             return 0;
        //         }
        //     }
        // }
        
        $totalSelfEarnings = 0;
        $totalReferralEarnings = 0;
        $processedInvestments = 0;
        
        try {
            // Use database transaction to ensure atomicity
            DB::transaction(function () use (&$totalSelfEarnings, &$totalReferralEarnings, &$processedInvestments, $today, $force) {
                // If force is used, clean up today's entries first
                if ($force) {
                    $this->cleanupTodayEntries($today);
                }
                
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
            
            $this->info('Earning update completed successfully!');
            $this->info("Date: {$today}");
            $this->info("Frequency: " . WithdrawalSetting::getEarningFrequency() . " ({$intervalsPerDay} intervals/day)");
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
     * Clean up today's entries when using --force
     */
    private function cleanupTodayEntries($today)
    {
        $this->info('Cleaning up today\'s entries for reprocessing...');
        
        // Fetch today's earning transactions before deleting so we can reverse wallet amounts
        $todayEarningTxs = \App\Models\Transaction::where('type', 'earning')
            ->whereDate('created_at', $today)
            ->get(['user_id', 'amount']);
        
        // Fetch today's referral_bonus transactions before deleting
        $todayReferralTxs = \App\Models\Transaction::where('type', 'referral_bonus')
            ->whereDate('created_at', $today)
            ->get(['user_id', 'amount']);
        
        // Fetch today's completed referral bonuses before deleting
        $todayReferralBonuses = \App\Models\ReferralBonus::where('status', 'completed')
            ->where(function ($query) use ($today) {
                $query->whereDate('processed_at', $today)
                      ->orWhere(function ($q) use ($today) {
                          $q->whereNull('processed_at')
                            ->whereDate('created_at', $today);
                      });
            })
            ->get(['referrer_id', 'amount']);
        
        // Delete today's earning transactions
        $deletedEarningTxs = \App\Models\Transaction::where('type', 'earning')
            ->whereDate('created_at', $today)
            ->delete();
        
        // Delete today's referral_bonus transactions (if any)
        $deletedReferralTxs = \App\Models\Transaction::where('type', 'referral_bonus')
            ->whereDate('created_at', $today)
            ->delete();
        
        // Delete today's completed referral bonuses
        $deletedReferralBonuses = \App\Models\ReferralBonus::where('status', 'completed')
            ->where(function ($query) use ($today) {
                $query->whereDate('processed_at', $today)
                      ->orWhere(function ($q) use ($today) {
                          $q->whereNull('processed_at')
                            ->whereDate('created_at', $today);
                      });
            })
            ->delete();
        
        // Reset last_earning_date for investments that were processed today
        $resetInvestments = Investment::whereDate('last_earning_date', $today)
            ->update(['last_earning_date' => null]);
        
        // Delete today's DailyBonusLog to allow reprocessing
        DailyBonusLog::where('process_date', $today)->delete();
        
        // Reverse wallet balances for today's earnings
        $this->reverseWalletBalances($todayEarningTxs, $todayReferralTxs, $todayReferralBonuses);
        
        $this->line("Deleted earning transactions: {$deletedEarningTxs}");
        $this->line("Deleted referral_bonus transactions: {$deletedReferralTxs}");
        $this->line("Deleted referral bonuses: {$deletedReferralBonuses}");
        $this->line("Reset investment last_earning_date: {$resetInvestments}");
        $this->line("Deleted today's DailyBonusLog entries");
    }
    
    /**
     * Reverse wallet balances for today's earnings
     */
    private function reverseWalletBalances($earningTxs, $referralTxs, $referralBonuses)
    {
        $this->info('Reversing wallet balances for today\'s earnings...');
        
        // Aggregate amounts per user
        $userAdjustments = [];
        
        foreach ($earningTxs as $tx) {
            $uid = $tx->user_id;
            $userAdjustments[$uid] = ($userAdjustments[$uid] ?? 0) - $tx->amount;
        }
        
        foreach ($referralTxs as $tx) {
            $uid = $tx->user_id;
            $userAdjustments[$uid] = ($userAdjustments[$uid] ?? 0) - $tx->amount;
        }
        
        foreach ($referralBonuses as $rb) {
            $uid = $rb->referrer_id;
            $userAdjustments[$uid] = ($userAdjustments[$uid] ?? 0) - $rb->amount;
        }
        
        // Apply adjustments to wallets with optimistic locking
        foreach ($userAdjustments as $userId => $adjustment) {
            $wallet = \App\Models\Wallet::where('user_id', $userId)->lockForUpdate()->first();
            if ($wallet) {
                $this->info("Wallet found for user {$userId}");
                $this->warn(sprintf("Adjustment: {$adjustment}", $wallet->balance));
                $wallet->balance = max(0, $wallet->balance + $adjustment);
                // $wallet->total_earnings = max(0, $wallet->total_earnings + $adjustment);
                $wallet->earned_amount = max(0, $wallet->earned_amount + $adjustment);
                $wallet->save();
                $this->line("Reversed {$adjustment} from user {$userId} wallet");
            } else {
                $this->line("Wallet not found for user {$userId}");
            }
        }
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

        $intervalsPerDay = WithdrawalSetting::getIntervalsPerDay();
        $today = now()->format('Y-m-d');

        // Get all active investments - duplicate check handled in createEarningTransaction
        $activeInvestments = Investment::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->lockForUpdate()
            ->get();

        foreach ($activeInvestments as $investment) {
            // Check if already processed today via transaction record
            $existingTransaction = \App\Models\Transaction::where('user_id', $investment->user_id)
                ->where('type', 'earning')
                ->where('description', 'like', "%#{$investment->id}")
                ->whereDate('created_at', $today)
                ->first();

            if ($existingTransaction) {
                $this->line("Skipping investment #{$investment->id} - already processed today");
                continue;
            }

            $dailyEarning = $investment->calculateEarningForInterval($intervalsPerDay);

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
                    $this->createEarningTransaction($investment->user_id, $dailyEarning, 'earning', $investment);
                } else {
                    $this->error("Wallet not found for user {$investment->user_id}");
                    continue;
                }

                $totalSelfEarnings += $dailyEarning;
                $processedInvestments++;

                $this->line("Processed investment #{$investment->id}: {$dailyEarning} for user {$investment->user_id}");
            } else {
                $this->line("Investment #{$investment->id} has zero earnings for today");
            }
        }

        $this->info("Self earnings processed: {$processedInvestments} investments");
    }
    
    /**
     * Process referral earnings by calling distribute-referral-bonus command
     */
    private function processReferralEarnings(&$totalReferralEarnings)
    {
        $this->info('Processing referral earnings...');

        // Call the distribute-referral-bonus command
        $exitCode = \Illuminate\Support\Facades\Artisan::call('app:distribute-referral-bonus');
        $output = \Illuminate\Support\Facades\Artisan::output();

        $this->line($output);

        // Try to extract total amount from output
        if (preg_match('/Total Amount\s*\|\s*([\d,\.]+)/', $output, $matches)) {
            $totalReferralEarnings = (float) str_replace(',', '', $matches[1]);
        }

        $this->info('Referral earnings processing completed.');
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
            'description' => "Earning from investment",
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
