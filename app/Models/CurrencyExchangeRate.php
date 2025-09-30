<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CurrencyExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'inverse_rate',
        'source',
        'last_updated',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'inverse_rate' => 'decimal:8',
        'last_updated' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->calculateInverseRate();
            $model->last_updated = now();
        });

        static::updating(function ($model) {
            $model->calculateInverseRate();
            $model->last_updated = now();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFromCurrency($query, string $currency)
    {
        return $query->where('from_currency', $currency);
    }

    public function scopeToCurrency($query, string $currency)
    {
        return $query->where('to_currency', $currency);
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('last_updated', '>=', now()->subMinutes($minutes));
    }

    public function fromCurrencyModel()
    {
        return $this->belongsTo(Currency::class, 'from_currency', 'symbol');
    }

    public function toCurrencyModel()
    {
        return $this->belongsTo(Currency::class, 'to_currency', 'symbol');
    }

    public function calculateInverseRate(): void
    {
        if ($this->rate > 0) {
            $this->inverse_rate = 1 / $this->rate;
        }
    }

    public function isOutdated(int $minutes = 60): bool
    {
        return $this->last_updated->diffInMinutes(now()) > $minutes;
    }

    public static function getRate(string $fromCurrency, string $toCurrency): ?float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $rate = self::active()
            ->fromCurrency($fromCurrency)
            ->toCurrency($toCurrency)
            ->first();

        return $rate ? (float) $rate->rate : null;
    }

    public static function convert(float $amount, string $fromCurrency, string $toCurrency): ?float
    {
        $rate = self::getRate($fromCurrency, $toCurrency);

        if ($rate === null) {
            return null;
        }

        return $amount * $rate;
    }

    public static function updateRate(string $fromCurrency, string $toCurrency, float $rate, string $source = 'manual'): self
    {
        return self::updateOrCreate(
            [
                'from_currency' => $fromCurrency,
                'to_currency' => $toCurrency,
            ],
            [
                'rate' => $rate,
                'source' => $source,
                'is_active' => true,
            ]
        );
    }
}