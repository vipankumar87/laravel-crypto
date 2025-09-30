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
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('investment_plan');
            $table->decimal('amount', 15, 2);
            $table->decimal('expected_return', 15, 2);
            $table->decimal('earned_amount', 15, 2)->default(0.00);
            $table->integer('duration_days');
            $table->decimal('daily_return_rate', 5, 2);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->timestamp('last_earning_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
