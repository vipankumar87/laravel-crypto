<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteNonSeederUsers extends Command
{
    protected $signature = 'users:delete-non-seeder {--force : Skip confirmation}';

    protected $description = 'Delete all users except those created by the seeder (system, admin, user)';

    protected array $seederUsernames = ['system', 'admin', 'user'];

    public function handle(): int
    {
        $usersToDelete = User::whereNotIn('username', $this->seederUsernames)->get();

        if ($usersToDelete->isEmpty()) {
            $this->info('No non-seeder users found. Nothing to delete.');
            return self::SUCCESS;
        }

        $this->warn("The following {$usersToDelete->count()} user(s) will be deleted:");
        $this->table(
            ['ID', 'Username', 'Email', 'Created At'],
            $usersToDelete->map(fn ($u) => [$u->id, $u->username, $u->email, $u->created_at])
        );

        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete these users?')) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        $count = User::whereNotIn('username', $this->seederUsernames)->delete();

        $this->info("Successfully deleted {$count} user(s).");

        return self::SUCCESS;
    }
}
