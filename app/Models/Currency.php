<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'network',
        'full_name',
        'icon',
        'min_transaction_amount',
        'max_transaction_amount',
        'transaction_fee',
        'transaction_fee_type',
        'withdrawal_fee',
        'is_active',
        'allow_deposits',
        'allow_withdrawals',
        'allow_investments',
        'decimal_places',
        'sort_order',
        'description',
        'contract_address',
        'network_config',
    ];

    protected $casts = [
        'min_transaction_amount' => 'decimal:8',
        'max_transaction_amount' => 'decimal:8',
        'transaction_fee' => 'decimal:8',
        'withdrawal_fee' => 'decimal:8',
        'is_active' => 'boolean',
        'allow_deposits' => 'boolean',
        'allow_withdrawals' => 'boolean',
        'allow_investments' => 'boolean',
        'decimal_places' => 'integer',
        'sort_order' => 'integer',
        'network_config' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForInvestments($query)
    {
        return $query->where('allow_investments', true)->where('is_active', true);
    }

    public function scopeForDeposits($query)
    {
        return $query->where('allow_deposits', true)->where('is_active', true);
    }

    public function scopeForWithdrawals($query)
    {
        return $query->where('allow_withdrawals', true)->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function exchangeRatesFrom()
    {
        return $this->hasMany(CurrencyExchangeRate::class, 'from_currency', 'symbol');
    }

    public function exchangeRatesTo()
    {
        return $this->hasMany(CurrencyExchangeRate::class, 'to_currency', 'symbol');
    }

    public function investments()
    {
        return $this->hasMany(Investment::class, 'payment_currency', 'symbol');
    }

    public function formatAmount(float $amount): string
    {
        return number_format($amount, $this->decimal_places, '.', '');
    }

    public function getDisplayName(): string
    {
        return $this->full_name ?: "{$this->name} ({$this->symbol})";
    }

    public function getNetworkDisplayName(): string
    {
        return $this->network ? "{$this->symbol} ({$this->network})" : $this->symbol;
    }
}