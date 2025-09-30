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
        Schema::table('investments', function (Blueprint $table) {
            $table->string('payment_currency', 10)->default('USDT')->after('amount'); // Currency used for payment
            $table->string('investment_currency', 10)->default('USDT')->after('payment_currency'); // Currency investment is in
            $table->decimal('payment_amount', 18, 8)->nullable()->after('investment_currency'); // Original payment amount
            $table->decimal('exchange_rate', 18, 8)->nullable()->after('payment_amount'); // Exchange rate used
            $table->decimal('conversion_fee', 18, 8)->default(0)->after('exchange_rate'); // Fee for currency conversion
            $table->string('payment_network')->nullable()->after('conversion_fee'); // Payment network used

            $table->index(['payment_currency']);
            $table->index(['investment_currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropIndex(['payment_currency']);
            $table->dropIndex(['investment_currency']);

            $table->dropColumn([
                'payment_currency',
                'investment_currency',
                'payment_amount',
                'exchange_rate',
                'conversion_fee',
                'payment_network'
            ]);
        });
    }
};