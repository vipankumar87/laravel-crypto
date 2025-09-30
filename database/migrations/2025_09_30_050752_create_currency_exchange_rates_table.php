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
        Schema::create('currency_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 10); // e.g., "USDT"
            $table->string('to_currency', 10); // e.g., "DOGE"
            $table->decimal('rate', 18, 8); // Exchange rate from_currency to to_currency
            $table->decimal('inverse_rate', 18, 8); // Inverse rate for faster lookups
            $table->string('source')->default('manual'); // 'manual', 'api', 'calculated'
            $table->timestamp('last_updated');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['from_currency', 'to_currency']);
            $table->index(['from_currency', 'is_active']);
            $table->index(['to_currency', 'is_active']);
            $table->index('last_updated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_exchange_rates');
    }
};