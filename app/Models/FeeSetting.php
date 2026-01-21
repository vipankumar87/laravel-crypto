<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FeeSetting extends Model
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
        'value' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    const CACHE_KEY = 'fee_settings';
    const CACHE_TTL = 3600; // 1 hour

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
        return (float) $setting->value;
    }

    public static function getType(string $name, $default = 'flat')
    {
        $setting = self::getSetting($name);
        if (!$setting) {
            return $default;
        }
        return $setting->type;
    }

    public static function isActive(string $name)
    {
        $setting = self::getSetting($name);
        return $setting && $setting->is_active;
    }

    /**
     * Calculate fee based on amount
     */
    public static function calculateFee(string $name, float $amount): float
    {
        $setting = self::getSetting($name);

        if (!$setting || !$setting->is_active) {
            return 0;
        }

        if ($setting->type === 'percentage') {
            return round($amount * ($setting->value / 100), 4);
        }

        return (float) $setting->value;
    }

    /**
     * Calculate total fees for an investment
     */
    public static function calculateTotalFees(float $amount): array
    {
        $platformFee = self::calculateFee('platform_fee', $amount);
        $transactionFee = self::calculateFee('transaction_fee', $amount);
        $totalFees = $platformFee + $transactionFee;

        return [
            'platform_fee' => $platformFee,
            'transaction_fee' => $transactionFee,
            'total_fees' => $totalFees,
            'net_amount' => $amount - $totalFees,
            'platform_fee_type' => self::getType('platform_fee'),
            'transaction_fee_type' => self::getType('transaction_fee'),
        ];
    }

    /**
     * Get fee breakdown for display
     */
    public static function getFeeBreakdown(): array
    {
        $settings = self::getAll();
        $breakdown = [];

        foreach ($settings as $setting) {
            $breakdown[$setting->name] = [
                'label' => $setting->label,
                'value' => $setting->value,
                'type' => $setting->type,
                'is_active' => $setting->is_active,
                'display' => $setting->type === 'percentage'
                    ? number_format($setting->value, 2) . '%'
                    : '$' . number_format($setting->value, 2),
            ];
        }

        return $breakdown;
    }

    /**
     * Clear fee settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
