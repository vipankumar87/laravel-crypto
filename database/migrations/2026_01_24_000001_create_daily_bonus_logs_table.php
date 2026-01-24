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
        Schema::create('daily_bonus_logs', function (Blueprint $table) {
            $table->id();
            $table->date('process_date')->unique(); // Unique date to prevent duplicates
            $table->decimal('total_self_earnings', 15, 2)->default(0.00);
            $table->decimal('total_referral_earnings', 15, 2)->default(0.00);
            $table->decimal('total_earnings', 15, 2)->default(0.00);
            $table->integer('processed_investments')->default(0);
            $table->timestamp('processed_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('process_date');
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_bonus_logs');
    }
};
