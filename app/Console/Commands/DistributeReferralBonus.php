<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Investment;
use App\Models\Wallet;
use App\Models\ReferralBonus;
use App\Models\ReferralLevelSetting;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DistributeReferralBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:distribute-referral-bonus
                            {--user= : Process only for specific user ID}
                            {--investment= : Process only for specific investment ID}
                            {--force : Force reprocessing even if already distributed}
                            {--dry-run : Show what would be done without executing}
                            {--history : Show recent referral bonus history}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribute referral bonuses to upline chain for investments';

    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        parent::__construct();
        $this->referralService = $referralService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting referral bonus distribution...');

        // Show history if requested
        if ($this->option('history')) {
            $this->showBonusHistory();
            return 0;
        }

        $targetUserId = $this->option('user');
        $targetInvestmentId = $this->option('investment');
        $force = $this->option('force');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $totalInvestmentsProcessed = 0;
        $totalBonusesDistributed = 0;
        $totalAmountDistributed = 0;

        try {
            DB::transaction(function () use (
                &$totalInvestmentsProcessed,
                &$totalBonusesDistributed,
                &$totalAmountDistributed,
                $targetUserId,
                $targetInvestmentId,
                $force,
                $isDryRun
            ) {
                // Build query for investments
                $query = Investment::where('status', 'active')
                    ->whereHas('user', function ($q) {
                        $q->whereNotNull('referred_by');
                    });

                if ($targetUserId) {
                    $query->where('user_id', $targetUserId);
                }

                if ($targetInvestmentId) {
                    $query->where('id', $targetInvestmentId);
                }

                // If not forcing, only get investments without referral bonuses
                if (!$force) {
                    $query->whereDoesntHave('referralBonuses');
                }

                $investments = $query->with(['user', 'user.wallet'])->get();

                $this->info("Found {$investments->count()} investments to process");

                foreach ($investments as $investment) {
                    $result = $this->processInvestment(
                        $investment,
                        $force,
                        $isDryRun,
                        $totalBonusesDistributed,
                        $totalAmountDistributed
                    );

                    if ($result) {
                        $totalInvestmentsProcessed++;
                    }
                }
            });

            $this->newLine();
            $this->info('Referral bonus distribution completed!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Investments Processed', $totalInvestmentsProcessed],
                    ['Bonuses Distributed', $totalBonusesDistributed],
                    ['Total Amount', number_format($totalAmountDistributed, 2) . ' DOGE'],
                ]
            );

            if ($isDryRun) {
                $this->warn('This was a dry run. No actual changes were made.');
            }

            Log::info('Referral bonus distribution completed', [
                'investments_processed' => $totalInvestmentsProcessed,
                'bonuses_distributed' => $totalBonusesDistributed,
                'total_amount' => $totalAmountDistributed,
                'forced' => $force,
                'dry_run' => $isDryRun,
            ]);

        } catch (\Exception $e) {
            $this->error('Error during referral bonus distribution: ' . $e->getMessage());
            Log::error('Referral bonus distribution failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Process a single investment for referral bonus distribution
     */
    private function processInvestment(
        $investment,
        $force,
        $isDryRun,
        &$totalBonusesDistributed,
        &$totalAmountDistributed
    ) {
        $user = $investment->user;

        if (!$user) {
            $this->line("  Skipping investment #{$investment->id} - user not found");
            return false;
        }

        if (!$user->referred_by) {
            $this->line("  Skipping investment #{$investment->id} - user has no referrer");
            return false;
        }

        // Check if bonuses already distributed (unless force)
        if (!$force) {
            $existingBonuses = ReferralBonus::where('investment_id', $investment->id)->count();
            if ($existingBonuses > 0) {
                $this->line("  Skipping investment #{$investment->id} - bonuses already distributed");
                return false;
            }
        }

        $this->info("Processing investment #{$investment->id} for user {$user->username} ({$user->name})");
        $this->line("  Investment Amount: {$investment->amount} DOGE");

        // Get upline chain
        $levelPercentages = $this->referralService->getLevelPercentages();
        $maxLevel = $this->referralService->getMaxLevel();
        $uplineChain = $user->getUplineChain($maxLevel);

        if (empty($uplineChain)) {
            $this->line("  No upline found for user");
            return false;
        }

        $bonusesCreated = 0;
        $amountDistributed = 0;

        foreach ($uplineChain as $level => $referrer) {
            if (!$referrer || !$referrer->wallet) {
                $this->line("  Level {$level}: Referrer has no wallet - skipping");
                continue;
            }

            $bonusPercentage = $levelPercentages[$level] ?? 0;
            if ($bonusPercentage <= 0) {
                $this->line("  Level {$level}: No bonus percentage configured - skipping");
                continue;
            }

            $bonusAmount = ($investment->amount * $bonusPercentage) / 100;

            // Check if this specific bonus already exists (skip if it exists and not forcing)
            $existingBonus = ReferralBonus::where('investment_id', $investment->id)
                ->where('referrer_id', $referrer->id)
                ->where('level', $level)
                ->first();

            if ($existingBonus) {
                if (!$force) {
                    $this->line("  Level {$level}: Bonus already exists for {$referrer->username} - skipping");
                    continue;
                } else {
                    // Delete existing bonus to allow reprocessing in force mode
                    $existingBonus->delete();
                    $this->line("  Level {$level}: Deleted existing bonus for {$referrer->username}, reprocessing...");
                }
            }

            if ($isDryRun) {
                $this->line("  Level {$level}: Would distribute {$bonusAmount} DOGE ({$bonusPercentage}%) to {$referrer->username}");
            } else {
                // Create referral bonus record
                $referralBonus = ReferralBonus::create([
                    'user_id' => $user->id,
                    'referrer_id' => $referrer->id,
                    'investment_id' => $investment->id,
                    'level' => $level,
                    'amount' => $bonusAmount,
                    'investment_amount' => $investment->amount,
                    'bonus_percentage' => $bonusPercentage,
                    'type' => 'investment',
                    'status' => 'completed',
                    'description' => "Level {$level} referral bonus from {$user->name}'s investment",
                    'processed_at' => now(),
                ]);

                // Add bonus to referrer's wallet
                $wallet = Wallet::where('user_id', $referrer->id)->lockForUpdate()->first();
                if ($wallet) {
                    $wallet->balance += $bonusAmount;
                    $wallet->referral_earnings = ($wallet->referral_earnings ?? 0) + $bonusAmount;
                    $wallet->save();

                    // Create transaction record
                    $this->createBonusTransaction($referrer->id, $bonusAmount, $level, $user, $investment);
                }

                $this->info("  Level {$level}: Distributed {$bonusAmount} DOGE ({$bonusPercentage}%) to {$referrer->username}");

                Log::info('Referral bonus distributed', [
                    'user_id' => $user->id,
                    'referrer_id' => $referrer->id,
                    'level' => $level,
                    'amount' => $bonusAmount,
                    'investment_id' => $investment->id,
                ]);
            }

            $bonusesCreated++;
            $amountDistributed += $bonusAmount;
        }

        $totalBonusesDistributed += $bonusesCreated;
        $totalAmountDistributed += $amountDistributed;

        $this->line("  Total: {$bonusesCreated} bonuses, {$amountDistributed} DOGE distributed");
        $this->newLine();

        return $bonusesCreated > 0;
    }

    /**
     * Create a transaction record for the referral bonus
     */
    private function createBonusTransaction($userId, $amount, $level, $fromUser, $investment)
    {
        return \App\Models\Transaction::create([
            'user_id' => $userId,
            'transaction_id' => 'REF_' . uniqid() . '_' . date('YmdHis'),
            'type' => 'referral_bonus',
            'amount' => $amount,
            'net_amount' => $amount,
            'status' => 'completed',
            'description' => "Level {$level} referral bonus from {$fromUser->username}'s investment #{$investment->id}",
            'processed_at' => now(),
        ]);
    }

    /**
     * Show recent referral bonus history
     */
    private function showBonusHistory()
    {
        $this->info('Recent Referral Bonus History (Last 30 days):');
        $this->newLine();

        // Get summary by level
        $summaryByLevel = ReferralBonus::where('status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('level, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        if ($summaryByLevel->isEmpty()) {
            $this->info('No referral bonuses found in the last 30 days.');
            return;
        }

        $this->info('Summary by Level:');
        $this->table(
            ['Level', 'Count', 'Total Amount'],
            $summaryByLevel->map(function ($item) {
                return [
                    'Level ' . $item->level,
                    $item->count,
                    number_format($item->total_amount, 2) . ' DOGE',
                ];
            })
        );

        $this->newLine();

        // Get recent bonuses
        $recentBonuses = ReferralBonus::with(['user', 'referrer', 'investment'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $this->info('Recent Bonuses (Last 20):');
        $this->table(
            ['Date', 'From User', 'To Referrer', 'Level', 'Amount', 'Investment'],
            $recentBonuses->map(function ($bonus) {
                return [
                    $bonus->created_at->format('Y-m-d H:i'),
                    $bonus->user->username ?? 'N/A',
                    $bonus->referrer->username ?? 'N/A',
                    $bonus->level,
                    number_format($bonus->amount, 2) . ' DOGE',
                    number_format($bonus->investment_amount ?? 0, 2) . ' DOGE',
                ];
            })
        );

        // Total stats
        $totalStats = ReferralBonus::where('status', 'completed')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount')
            ->first();

        $this->newLine();
        $this->info('All Time Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Bonuses', $totalStats->count ?? 0],
                ['Total Amount', number_format($totalStats->total_amount ?? 0, 2) . ' DOGE'],
            ]
        );
    }
}
