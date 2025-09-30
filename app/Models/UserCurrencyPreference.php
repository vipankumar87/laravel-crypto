<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCurrencyPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'preferred_payment_currency',
        'preferred_investment_currency',
        'preferred_display_currency',
        'auto_convert',
        'enabled_currencies',
    ];

    protected $casts = [
        'auto_convert' => 'boolean',
        'enabled_currencies' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentCurrency()
    {
        return $this->belongsTo(Currency::class, 'preferred_payment_currency', 'symbol');
    }

    public function investmentCurrency()
    {
        return $this->belongsTo(Currency::class, 'preferred_investment_currency', 'symbol');
    }

    public function displayCurrency()
    {
        return $this->belongsTo(Currency::class, 'preferred_display_currency', 'symbol');
    }

    public function getEnabledCurrenciesAttribute($value)
    {
        if (empty($value)) {
            return ['USDT', 'BTC', 'ETH', 'SOL', 'DOGE']; // Default currencies
        }

        return json_decode($value, true) ?: [];
    }

    public function isCurrencyEnabled(string $currency): bool
    {
        return in_array($currency, $this->enabled_currencies);
    }

    public function enableCurrency(string $currency): void
    {
        $currencies = $this->enabled_currencies;
        if (!in_array($currency, $currencies)) {
            $currencies[] = $currency;
            $this->enabled_currencies = $currencies;
            $this->save();
        }
    }

    public function disableCurrency(string $currency): void
    {
        $currencies = $this->enabled_currencies;
        $index = array_search($currency, $currencies);
        if ($index !== false) {
            unset($currencies[$index]);
            $this->enabled_currencies = array_values($currencies);
            $this->save();
        }
    }

    public static function getForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'preferred_payment_currency' => 'USDT',
                'preferred_investment_currency' => 'USDT',
                'preferred_display_currency' => 'USDT',
                'auto_convert' => true,
                'enabled_currencies' => ['USDT', 'BTC', 'ETH', 'SOL', 'DOGE'],
            ]
        );
    }
}