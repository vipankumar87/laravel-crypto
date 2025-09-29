<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Add nullable username column first
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->after('name');
            }
        });

        // 2) Backfill usernames for existing users
        DB::transaction(function () {
            $users = DB::table('users')->select('id', 'username', 'name', 'email')->get();

            $existing = [];
            foreach ($users as $u) {
                if (!empty($u->username)) {
                    $existing[$u->username] = true;
                }
            }

            foreach ($users as $u) {
                if (empty($u->username)) {
                    // Prefer a slug of name, otherwise from email prefix, otherwise 'user'
                    $base = $u->name ? Str::slug($u->name) : null;
                    if (!$base && $u->email) {
                        $base = Str::slug(explode('@', $u->email)[0]);
                    }
                    if (!$base) {
                        $base = 'user';
                    }

                    $candidate = $base;
                    $suffix = 1;
                    while (isset($existing[$candidate])) {
                        $candidate = $base.'-'.$suffix;
                        $suffix++;
                    }

                    DB::table('users')->where('id', $u->id)->update(['username' => $candidate]);
                    $existing[$candidate] = true;
                }
            }
        });

        // 3) Add unique index to username
        Schema::table('users', function (Blueprint $table) {
            // Only add if not exists (some DBs don't support direct check; the try-catch guards runtime)
            try {
                $table->unique('username');
            } catch (\Throwable $e) {
                // ignore if already exists
            }
        });

        // 4) Drop unique constraint on email to allow multiple accounts per email
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropUnique('users_email_unique');
            } catch (\Throwable $e) {
                // On some setups, the index name might differ; fallback attempt
                try {
                    $table->dropUnique(['email']);
                } catch (\Throwable $e2) {
                    // ignore if it doesn't exist
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1) Re-add unique to email
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->unique('email');
            } catch (\Throwable $e) {
                // ignore
            }
        });

        // 2) Drop unique and column username
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropUnique('users_username_unique');
            } catch (\Throwable $e) {
                try {
                    $table->dropUnique(['username']);
                } catch (\Throwable $e2) {
                    // ignore
                }
            }

            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};
