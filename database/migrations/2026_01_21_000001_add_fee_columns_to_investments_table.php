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
            $table->decimal('platform_fee', 15, 4)->default(0)->after('amount');
            $table->decimal('transaction_fee', 15, 4)->default(0)->after('platform_fee');
            $table->decimal('total_fees', 15, 4)->default(0)->after('transaction_fee');
            $table->decimal('gross_amount', 15, 4)->nullable()->after('total_fees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn(['platform_fee', 'transaction_fee', 'total_fees', 'gross_amount']);
        });
    }
};
