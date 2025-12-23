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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Tether USD"
            $table->string('symbol', 10)->unique(); // e.g., "USDT"
            $table->string('network')->nullable(); // e.g., "BEP20", "ERC20", "SOL"
            $table->string('full_name')->nullable(); // e.g., "BEP20 USDT"
            $table->string('icon')->nullable(); // Currency icon/logo path
            $table->decimal('min_transaction_amount', 18, 8)->default(0.00000001);
            $table->decimal('max_transaction_amount', 18, 8)->nullable();
            $table->decimal('transaction_fee', 18, 8)->default(0);
            $table->string('transaction_fee_type', 20)->default('fixed'); // 'fixed' or 'percentage'
            $table->decimal('withdrawal_fee', 18, 8)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_deposits')->default(true);
            $table->boolean('allow_withdrawals')->default(true);
            $table->boolean('allow_investments')->default(true);
            $table->integer('decimal_places')->default(8);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->string('contract_address')->nullable(); // Smart contract address
            $table->json('network_config')->nullable(); // Additional network configuration
            $table->timestamps();

            $table->index(['symbol', 'network']);
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};