<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class FixUserPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:user-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix missing encrypted passwords for existing users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('encrypted_password')->get();

        if ($users->count() === 0) {
            $this->info('No users found with missing encrypted passwords.');
            return;
        }

        $this->info('Found ' . $users->count() . ' users without encrypted passwords.');

        foreach ($users as $user) {
            // Default password for demonstration - in real scenario you might have different logic
            $defaultPassword = 'password';
            $user->encrypted_password = encrypt($defaultPassword);
            $user->save();

            $this->info("Updated user: {$user->name} ({$user->email})");
        }

        $this->info('All users have been updated with encrypted passwords.');
    }
}
