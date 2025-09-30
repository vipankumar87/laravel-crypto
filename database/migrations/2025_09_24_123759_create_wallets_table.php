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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->decimal('invested_amount', 15, 2)->default(0.00);
            $table->decimal('earned_amount', 15, 2)->default(0.00);
            $table->decimal('withdrawn_amount', 15, 2)->default(0.00);
            $table->decimal('referral_earnings', 15, 2)->default(0.00);
            $table->string('wallet_address')->unique()->nullable();
            $table->enum('status', ['active', 'suspended', 'frozen'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
