<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdrawal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('value')->default('0');
            $table->enum('type', ['number', 'boolean', 'string'])->default('number');
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('withdrawal_settings')->insert([
            [
                'name' => 'min_usdt_threshold',
                'label' => 'Min USDT Threshold',
                'value' => '50',
                'type' => 'number',
                'is_active' => true,
                'description' => 'Minimum total earnings required before USDT withdrawal is allowed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'max_withdrawal_amount',
                'label' => 'Max Withdrawal Amount',
                'value' => '10000',
                'type' => 'number',
                'is_active' => true,
                'description' => 'Maximum amount allowed per withdrawal request',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'auto_approve_enabled',
                'label' => 'Auto Approve Enabled',
                'value' => '0',
                'type' => 'boolean',
                'is_active' => true,
                'description' => 'Enable automatic approval for withdrawals',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'auto_approve_threshold',
                'label' => 'Auto Approve Threshold',
                'value' => '100',
                'type' => 'number',
                'is_active' => true,
                'description' => 'Withdrawals at or below this amount are auto-approved (when enabled)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'withdrawal_fee',
                'label' => 'Withdrawal Fee',
                'value' => '0',
                'type' => 'number',
                'is_active' => true,
                'description' => 'Fee charged on USDT withdrawals',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'withdrawal_fee_type',
                'label' => 'Withdrawal Fee Type',
                'value' => 'flat',
                'type' => 'string',
                'is_active' => true,
                'description' => 'Fee type: flat or percentage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'doge_bonus_threshold',
                'label' => 'DOGE Bonus Threshold',
                'value' => '1000',
                'type' => 'number',
                'is_active' => true,
                'description' => 'Total earnings required to qualify for DOGE bonus',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'doge_bonus_amount',
                'label' => 'DOGE Bonus Amount',
                'value' => '100',
                'type' => 'number',
                'is_active' => true,
                'description' => 'Amount of DOGE awarded as bonus (one-time)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_settings');
    }
};
