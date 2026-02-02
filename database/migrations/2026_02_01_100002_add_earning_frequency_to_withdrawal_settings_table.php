<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\WithdrawalSetting;

return new class extends Migration
{
    public function up(): void
    {
        WithdrawalSetting::firstOrCreate(
            ['name' => 'earning_frequency'],
            [
                'label' => 'Earning Frequency',
                'value' => 'daily',
                'type' => 'string',
                'is_active' => true,
                'description' => 'How often earnings are credited: daily, twice_daily, every_5_hours, hourly, every_30_min, every_15_min, every_5_min, every_minute',
            ]
        );
    }

    public function down(): void
    {
        WithdrawalSetting::where('name', 'earning_frequency')->delete();
    }
};
