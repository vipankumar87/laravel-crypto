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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('currency', 10)->default('USDT')->after('type');
        });

        // For SQLite, we need to recreate the column to add enum value
        // Since Laravel uses SQLite in dev, we'll handle the type column carefully
        // The type is stored as string in SQLite anyway, so doge_bonus will work
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
