<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to alter the ENUM column
        DB::statement("ALTER TABLE investments MODIFY COLUMN status ENUM('active', 'completed', 'cancelled', 'pending') DEFAULT 'active'");

        // Add payment_method column
        Schema::table('investments', function (Blueprint $table) {
            $table->string('payment_method')->default('wallet')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove payment_method column
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });

        // Revert status ENUM to original values
        DB::statement("ALTER TABLE investments MODIFY COLUMN status ENUM('active', 'completed', 'cancelled') DEFAULT 'active'");
    }
};
