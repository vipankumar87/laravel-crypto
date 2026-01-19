<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserTransaction;
use App\Models\User;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AutoAdjustRealTimePaymentToInvestors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-adjust-real-time-payment-to-investors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-adjust real-time payments to investors based on received transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-adjust payment process...');

        try {
            // Get recent transactions that need processing (not yet linked to investments)
            $transactions = UserTransaction::where('status', 'transferred')
                ->whereNull('invests_id')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($transactions->isEmpty()) {
                $this->info('No new transactions to process.');
                return 0;
            }

            $this->info("Processing {$transactions->count()} transaction(s)...");

            $processedCount = 0;

            foreach ($transactions as $transaction) {
                try {
                    // Mark transaction as read
                    $transaction->update(['is_read' => true]);

                    // Get user
                    $user = User::find($transaction->user_id);
                    
                    if (!$user) {
                        $this->warn("User not found for transaction ID: {$transaction->id}");
                        continue;
                    }

                    // Convert USDT amount to Dogecoin value

                    $usdtAmount = floatval($transaction->amount);
                    $conversionResult = $this->convertUsdtToDogecoin($usdtAmount);
                    
                    if (!$conversionResult) {
                        $this->warn("Failed to convert USDT to Dogecoin for transaction ID: {$transaction->id}");
                        continue;
                    }
                    
                    $dogecoinValue = $conversionResult['doge_value'];
                    $dogeRate = $conversionResult['doge_rate'];
                    
                    $this->info(sprintf("Processing: %f USDT = %f DOGE (Rate: 1 DOGE = $%f)", $usdtAmount, $dogecoinValue, $dogeRate));
                    
                    // Get investment plan based on USDT amount (not DOGE)
                    $investmentPlan = InvestmentPlan::where('status', 'active')
                        ->where('min_amount', '<=', $usdtAmount)
                        ->where(function($query) use ($usdtAmount) {
                            $query->whereNull('max_amount')
                                  ->orWhere('max_amount', '>=', $usdtAmount);
                        })
                        ->orderBy('daily_return_rate', 'desc')
                        ->first();
                    
                    if (!$investmentPlan) {
                        $this->warn("No suitable investment plan found for amount {$usdtAmount} USDT");
                        continue;
                    }
                    
                    // Create investment
                    $expectedReturn = $dogecoinValue * ($investmentPlan->total_return_rate / 100);
                    $startDate = now();
                    $endDate = now()->addDays($investmentPlan->duration_days);
                    
                    $investment = Investment::create([
                        'user_id' => $user->id,
                        'investment_plan_id' => $investmentPlan->id,
                        'investment_plan' => $investmentPlan->name,
                        'amount' => $dogecoinValue,
                        'doge_rate' => $dogeRate,
                        'expected_return' => $expectedReturn,
                        'earned_amount' => 0,
                        'duration_days' => $investmentPlan->duration_days,
                        'daily_return_rate' => $investmentPlan->daily_return_rate,
                        'status' => 'active',
                        'payment_method' => 'crypto',
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]);
                    
                    // Link transaction to investment
                    $transaction->update(['invests_id' => $investment->id]);
                    
                    // Update wallet invested_amount
                    if ($user->wallet) {
                        $user->wallet->increment('invested_amount', $dogecoinValue);
                    } else {
                        \App\Models\Wallet::create([
                            'user_id' => $user->id,
                            'balance' => 0,
                            'invested_amount' => $dogecoinValue,
                            'earned_amount' => 0,
                            'withdrawn_amount' => 0,
                        ]);
                    }
                    
                    $this->line("✅ Created investment ID {$investment->id} for user {$user->username}");
                    $this->line("   USDT: {$usdtAmount} → DOGE: {$dogecoinValue}");
                    $this->line("   Plan: {$investmentPlan->name} | Expected Return: {$expectedReturn} DOGE");
                    
                    $processedCount++;
                    
                } catch (\Exception $e) {
                    $this->error("Error processing transaction ID {$transaction->id}: " . $e->getMessage());
                }
            }

            $this->newLine();
            $this->info("✅ Auto-adjust completed! Processed {$processedCount} transaction(s).");

            return 0;

        } catch (\Exception $e) {
            $this->error("Auto-adjust failed: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Convert USDT amount to Dogecoin value using real-time exchange rates
     *
     * @param float $usdtAmount
     * @return array|null ['doge_value' => float, 'doge_rate' => float]
     */
    private function convertUsdtToDogecoin(float $usdtAmount): ?array
    {
        try {
            // Try CoinGecko API first (free, no API key required)
            $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'dogecoin',
                'vs_currencies' => 'usd'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['dogecoin']['usd'])) {
                    $dogePrice = floatval($data['dogecoin']['usd']);
                    
                    if ($dogePrice > 0) {
                        $dogecoinValue = $usdtAmount / $dogePrice;
                        $this->info("Exchange rate: 1 DOGE = ${dogePrice} USD");
                        return [
                            'doge_value' => round($dogecoinValue, 8),
                            'doge_rate' => $dogePrice
                        ];
                    }
                }
            }

            // Fallback to Binance API
            $response = Http::timeout(10)->get('https://api.binance.com/api/v3/ticker/price', [
                'symbol' => 'DOGEUSDT'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['price'])) {
                    $dogePrice = floatval($data['price']);
                    
                    if ($dogePrice > 0) {
                        $dogecoinValue = $usdtAmount / $dogePrice;
                        $this->info("Exchange rate (Binance): 1 DOGE = ${dogePrice} USDT");
                        return [
                            'doge_value' => round($dogecoinValue, 8),
                            'doge_rate' => $dogePrice
                        ];
                    }
                }
            }

            // If both APIs fail, use a fallback rate from environment or default
            $fallbackRate = env('DOGE_USD_FALLBACK_RATE', 0.08); // Default ~$0.08 per DOGE
            $this->warn("Using fallback exchange rate: 1 DOGE = ${fallbackRate} USD");
            $dogecoinValue = $usdtAmount / $fallbackRate;
            return [
                'doge_value' => round($dogecoinValue, 8),
                'doge_rate' => $fallbackRate
            ];

        } catch (\Exception $e) {
            $this->error("Error fetching exchange rate: " . $e->getMessage());
            
            // Use fallback rate
            $fallbackRate = env('DOGE_USD_FALLBACK_RATE', 0.08);
            $this->warn("Using fallback exchange rate: 1 DOGE = ${fallbackRate} USD");
            $dogecoinValue = $usdtAmount / $fallbackRate;
            return [
                'doge_value' => round($dogecoinValue, 8),
                'doge_rate' => $fallbackRate
            ];
        }
    }
}
