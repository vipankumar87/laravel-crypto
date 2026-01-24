<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Investment;
use App\Models\Wallet;
use App\Models\ReferralBonus;
use App\Models\ReferralLevelSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateDailyBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-daily-bonus';

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
        
        $totalSelfEarnings = 0;
        $totalReferralEarnings = 0;
        $processedInvestments = 0;
        
        try {
            DB::beginTransaction();
            
            // Process self (direct) earnings from active investments
            $this->processSelfEarnings($totalSelfEarnings, $processedInvestments);
            
            // Process referral earnings (if any new logic needed)
            $this->processReferralEarnings($totalReferralEarnings);
            
            DB::commit();
            
            $this->info('Daily bonus update completed successfully!');
            $this->info("Processed Investments: {$processedInvestments}");
            $this->info("Total Self Earnings: {$totalSelfEarnings}");
            $this->info("Total Referral Earnings: {$totalReferralEarnings}");
            $this->info("Total Earnings: " . ($totalSelfEarnings + $totalReferralEarnings));
            
            Log::info('Daily bonus update completed', [
                'self_earnings' => $totalSelfEarnings,
                'referral_earnings' => $totalReferralEarnings,
                'processed_investments' => $processedInvestments
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during daily bonus update: ' . $e->getMessage());
            Log::error('Daily bonus update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Process self (direct) earnings from active investments
     */
    private function processSelfEarnings(&$totalSelfEarnings, &$processedInvestments)
    {
        $this->info('Processing self (direct) earnings...');
        
        // Get active investments that haven't been processed today
        $activeInvestments = Investment::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where(function($query) {
                $query->whereNull('last_earning_date')
                      ->orWhere('last_earning_date', '<', now()->startOfDay());
            })
            ->get();
            
        foreach ($activeInvestments as $investment) {
            $dailyEarning = $investment->calculateDailyEarning();
            
            if ($dailyEarning > 0) {
                // Update investment earned amount and last earning date
                $investment->earned_amount += $dailyEarning;
                $investment->last_earning_date = now();
                $investment->save();
                
                // Update wallet earned amount
                $wallet = Wallet::where('user_id', $investment->user_id)->first();
                if ($wallet) {
                    $wallet->earned_amount += $dailyEarning;
                    $wallet->balance += $dailyEarning;
                    $wallet->save();
                    
                    // Create transaction record for the earning
                    $this->createEarningTransaction($investment->user_id, $dailyEarning, 'daily_return', $investment);
                }
                
                $totalSelfEarnings += $dailyEarning;
                $processedInvestments++;
                
                $this->line("Processed investment #{$investment->id}: {$dailyEarning} for user {$investment->user_id}");
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
        $transaction = \App\Models\Transaction::create([
            'user_id' => $userId,
            'transaction_id' => 'EARN_' . uniqid() . '_' . date('Ymd'),
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
