<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InvestmentPlan;

class InvestmentPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter Plan',
                'description' => 'Perfect for beginners looking to start their crypto investment journey. Low risk with steady returns.',
                'min_amount' => 50.00,
                'max_amount' => 500.00,
                'daily_return_rate' => 1.50,
                'duration_days' => 30,
                'total_return_rate' => 45.00,
                'referral_bonus_rate' => 3.00,
                'max_investors' => 1000,
                'status' => 'active',
            ],
            [
                'name' => 'Basic Plan',
                'description' => 'Ideal for intermediate investors seeking balanced growth and moderate risk exposure.',
                'min_amount' => 500.00,
                'max_amount' => 2000.00,
                'daily_return_rate' => 2.00,
                'duration_days' => 45,
                'total_return_rate' => 90.00,
                'referral_bonus_rate' => 5.00,
                'max_investors' => 800,
                'status' => 'active',
            ],
            [
                'name' => 'Premium Plan',
                'description' => 'Advanced investment option for experienced traders who can handle higher risks for better returns.',
                'min_amount' => 2000.00,
                'max_amount' => 10000.00,
                'daily_return_rate' => 2.50,
                'duration_days' => 60,
                'total_return_rate' => 150.00,
                'referral_bonus_rate' => 7.00,
                'max_investors' => 500,
                'status' => 'active',
            ],
            [
                'name' => 'Elite Plan',
                'description' => 'Exclusive high-yield investment plan for VIP investors. Maximum returns with premium support.',
                'min_amount' => 10000.00,
                'max_amount' => 50000.00,
                'daily_return_rate' => 3.00,
                'duration_days' => 90,
                'total_return_rate' => 270.00,
                'referral_bonus_rate' => 10.00,
                'max_investors' => 100,
                'status' => 'active',
            ],
            [
                'name' => 'Diamond Plan',
                'description' => 'Ultimate investment tier for institutional and whale investors. Highest returns and exclusive benefits.',
                'min_amount' => 50000.00,
                'max_amount' => 500000.00,
                'daily_return_rate' => 3.50,
                'duration_days' => 120,
                'total_return_rate' => 420.00,
                'referral_bonus_rate' => 15.00,
                'max_investors' => 50,
                'status' => 'active',
            ],
        ];

        foreach ($plans as $plan) {
            InvestmentPlan::create($plan);
        }
    }
}
