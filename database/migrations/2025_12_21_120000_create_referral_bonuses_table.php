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
        Schema::create('referral_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('investment_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('level')->comment('Referral level (1-5)');
            $table->decimal('amount', 15, 2);
            $table->decimal('investment_amount', 15, 2)->comment('Original investment amount that triggered bonus');
            $table->decimal('bonus_percentage', 5, 2)->comment('Percentage of bonus for this level');
            $table->string('type')->default('investment')->comment('Type: investment, deposit, etc.');
            $table->string('status')->default('completed')->comment('Status: pending, completed, cancelled');
            $table->text('description')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['referrer_id', 'level']);
            $table->index(['investment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_bonuses');
    }
};
