<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WalletService;
use App\Models\User;

class GenerateCryptoWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:generate-wallets {--force : Force regenerate wallets for all users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate crypto wallets for users who don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $walletService = new WalletService();

        $query = User::query();

        if ($this->option('force')) {
            $users = $query->get();
            $this->warn('Force mode enabled. Regenerating wallets for ALL users...');
        } else {
            $users = $query->whereNull('crypto_address')->get();
        }

        if ($users->isEmpty()) {
            $this->info('No users found without crypto wallets.');
            return 0;
        }

        $this->info("Generating wallets for {$users->count()} users...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            try {
                $wallet = $walletService->generateUserWallet($user->id);

                $user->update([
                    'crypto_address' => $wallet['address'],
                    'private_key' => $walletService->encryptPrivateKey($wallet['private_key']),
                    'public_key' => $wallet['public_key'],
                ]);

                $successCount++;
                $this->newLine();
                $this->line("✅ User #{$user->id} ({$user->username}): {$wallet['address']}");
            } catch (\Exception $e) {
                $errorCount++;
                $this->newLine();
                $this->error("❌ Failed for user #{$user->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Wallet generation completed!");
        $this->info("✅ Success: {$successCount}");
        if ($errorCount > 0) {
            $this->error("❌ Errors: {$errorCount}");
        }

        return 0;
    }
}
