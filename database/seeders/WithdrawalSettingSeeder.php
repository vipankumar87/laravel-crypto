<?php

namespace Database\Seeders;

use App\Models\WithdrawalSetting;
use Illuminate\Database\Seeder;

class WithdrawalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'name' => 'min_usdt_threshold',
                'label' => 'Min USDT Threshold',
                'value' => '50',
                'type' => 'number',
                'description' => 'Minimum total earnings required before USDT withdrawal is allowed',
            ],
            [
                'name' => 'max_withdrawal_amount',
                'label' => 'Max Withdrawal Amount',
                'value' => '10000',
                'type' => 'number',
                'description' => 'Maximum amount allowed per withdrawal request',
            ],
            [
                'name' => 'auto_approve_enabled',
                'label' => 'Auto Approve Enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable automatic approval for withdrawals',
            ],
            [
                'name' => 'auto_approve_threshold',
                'label' => 'Auto Approve Threshold',
                'value' => '100',
                'type' => 'number',
                'description' => 'Withdrawals at or below this amount are auto-approved (when enabled)',
            ],
            [
                'name' => 'withdrawal_fee',
                'label' => 'Withdrawal Fee',
                'value' => '0',
                'type' => 'number',
                'description' => 'Fee charged on USDT withdrawals',
            ],
            [
                'name' => 'withdrawal_fee_type',
                'label' => 'Withdrawal Fee Type',
                'value' => 'flat',
                'type' => 'string',
                'description' => 'Fee type: flat or percentage',
            ],
            [
                'name' => 'doge_bonus_threshold',
                'label' => 'DOGE Bonus Threshold',
                'value' => '1000',
                'type' => 'number',
                'description' => 'Total earnings required to qualify for DOGE bonus',
            ],
            [
                'name' => 'doge_bonus_amount',
                'label' => 'DOGE Bonus Amount',
                'value' => '100',
                'type' => 'number',
                'description' => 'Amount of DOGE awarded as bonus (one-time)',
            ],
            [
                'name' => 'earning_frequency',
                'label' => 'Earning Frequency',
                'value' => 'daily',
                'type' => 'string',
                'description' => 'How often earnings are credited: daily, twice_daily, every_5_hours, hourly, every_30_min, every_15_min, every_5_min, every_minute',
            ],
        ];

        foreach ($settings as $setting) {
            WithdrawalSetting::firstOrCreate(
                ['name' => $setting['name']],
                array_merge($setting, ['is_active' => true])
            );
        }
    }
}
