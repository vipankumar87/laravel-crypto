<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WalletService;
use App\Models\User;

class ViewUserKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crypto:view-keys {user_id : The ID of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View decrypted private and public keys for a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        // Find the user
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        // Check if user has crypto wallet
        if (empty($user->crypto_address)) {
            $this->error("User #{$userId} ({$user->username}) does not have a crypto wallet.");
            $this->info("You can generate one using: php artisan crypto:generate-wallets");
            return 1;
        }

        $this->info("User Information:");
        $this->line("─────────────────────────────────────────────────────────");
        $this->line("User ID:      {$user->id}");
        $this->line("Username:     {$user->username}");
        $this->line("Email:        {$user->email}");
        $this->newLine();

        $this->info("Wallet Information:");
        $this->line("─────────────────────────────────────────────────────────");
        $this->line("Address:      {$user->crypto_address}");
        $this->newLine();

        // Decrypt private key
        try {
            $walletService = new WalletService();
            $decryptedPrivateKey = $walletService->decryptPrivateKey($user->private_key);

            $this->warn("SENSITIVE DATA - Keep this information secure!");
            $this->line("─────────────────────────────────────────────────────────");
            $this->line("Public Key:   {$user->public_key}");
            $this->line("Private Key:  {$decryptedPrivateKey}");
            $this->line("─────────────────────────────────────────────────────────");
            $this->newLine();

            $this->warn("⚠️  Never share the private key with anyone!");

            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to decrypt private key: " . $e->getMessage());
            return 1;
        }
    }
}
