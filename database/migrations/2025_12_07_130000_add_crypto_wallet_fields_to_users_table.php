<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('crypto_address')->nullable()->after('bep_wallet_address');
            $table->text('private_key')->nullable()->after('crypto_address');
            $table->text('public_key')->nullable()->after('private_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['crypto_address', 'private_key', 'public_key']);
        });
    }
};
