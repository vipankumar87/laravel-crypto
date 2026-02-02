<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->decimal('monthly_bonus_earned', 15, 2)->default(0)->after('earned_amount');
            $table->date('last_monthly_bonus_date')->nullable()->after('monthly_bonus_earned');
        });
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropColumn(['monthly_bonus_earned', 'last_monthly_bonus_date']);
        });
    }
};
