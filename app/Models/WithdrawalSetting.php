<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class WithdrawalSetting extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'label',
        'value',
        'type',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const CACHE_KEY = 'withdrawal_settings';
    const CACHE_TTL = 3600; // 1 hour

    const CORE_SETTINGS = [
        'min_usdt_threshold',
        'max_withdrawal_amount',
        'auto_approve_enabled',
        'auto_approve_threshold',
        'withdrawal_fee',
        'withdrawal_fee_type',
        'doge_bonus_threshold',
        'doge_bonus_amount',
        'earning_frequency',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'value', 'type', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Static helpers
    public static function getAll()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::all()->keyBy('name');
        });
    }

    public static function getSetting(string $name)
    {
        $settings = self::getAll();
        return $settings->get($name);
    }

    public static function getValue(string $name, $default = 0)
    {
        $setting = self::getSetting($name);
        if (!$setting || !$setting->is_active) {
            return $default;
        }

        if ($setting->type === 'boolean') {
            return (bool) $setting->value;
        }

        if ($setting->type === 'number') {
            return (float) $setting->value;
        }

        return $setting->value;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // Domain helpers
    public static function isAutoApproveEnabled(): bool
    {
        return (bool) self::getValue('auto_approve_enabled', false);
    }

    public static function getAutoApproveThreshold(): float
    {
        return (float) self::getValue('auto_approve_threshold', 100);
    }

    public static function getMinUsdtThreshold(): float
    {
        return (float) self::getValue('min_usdt_threshold', 50);
    }

    public static function getMaxWithdrawalAmount(): float
    {
        return (float) self::getValue('max_withdrawal_amount', 10000);
    }

    public static function getDogeBonusThreshold(): float
    {
        return (float) self::getValue('doge_bonus_threshold', 1000);
    }

    public static function getEarningFrequency(): string
    {
        return (string) self::getValue('earning_frequency', 'daily');
    }

    public static function getIntervalsPerDay(): int
    {
        $map = [
            'daily' => 1,
            'twice_daily' => 2,
            'every_5_hours' => 5,
            'hourly' => 24,
            'every_30_min' => 48,
            'every_15_min' => 96,
            'every_5_min' => 288,
            'every_minute' => 1440,
        ];

        return $map[self::getEarningFrequency()] ?? 1;
    }

    public static function getIntervalMinutes(): int
    {
        $map = [
            'daily' => 1440,
            'twice_daily' => 720,
            'every_5_hours' => 300,
            'hourly' => 60,
            'every_30_min' => 30,
            'every_15_min' => 15,
            'every_5_min' => 5,
            'every_minute' => 1,
        ];

        return $map[self::getEarningFrequency()] ?? 1440;
    }

    public static function getDogeBonusAmount(): float
    {
        return (float) self::getValue('doge_bonus_amount', 100);
    }

    /**
     * Determine if a withdrawal should be auto-approved.
     */
    public static function shouldAutoApprove(float $amount): bool
    {
        if (!self::isAutoApproveEnabled()) {
            return false;
        }

        return $amount <= self::getAutoApproveThreshold();
    }

    /**
     * Calculate withdrawal fee for a given amount.
     */
    public static function calculateWithdrawalFee(float $amount): float
    {
        $feeValue = (float) self::getValue('withdrawal_fee', 0);
        $feeType = self::getValue('withdrawal_fee_type', 'flat');

        if ($feeValue <= 0) {
            return 0;
        }

        if ($feeType === 'percentage') {
            return round($amount * ($feeValue / 100), 2);
        }

        return $feeValue;
    }
}
