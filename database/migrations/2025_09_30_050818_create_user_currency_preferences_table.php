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
        Schema::create('user_currency_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('preferred_payment_currency', 10)->default('USDT'); // Currency user prefers to pay with
            $table->string('preferred_investment_currency', 10)->default('USDT'); // Currency user prefers investments in
            $table->string('preferred_display_currency', 10)->default('USDT'); // Currency for displaying amounts
            $table->boolean('auto_convert')->default(true); // Auto convert payment to investment currency
            $table->json('enabled_currencies')->nullable(); // Array of currencies user wants to use
            $table->timestamps();

            $table->unique('user_id');
            $table->index('preferred_payment_currency');
            $table->index('preferred_investment_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_currency_preferences');
    }
};