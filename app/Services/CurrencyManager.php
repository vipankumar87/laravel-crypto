<?php

namespace App\Services;

use App\Services\Currencies\AbstractCryptoCurrency;
use App\Services\Currencies\BEP20USDT;
use App\Services\Currencies\Dogecoin;
use App\Services\Currencies\Solana;
use App\Services\Currencies\Bitcoin;
use App\Services\Currencies\Ethereum;
use App\Models\Currency;

class CurrencyManager
{
    private array $currencies = [];

    public function __construct()
    {
        $this->registerCurrencies();
    }

    private function registerCurrencies(): void
    {
        $this->currencies = [
            'USDT_BEP20' => new BEP20USDT(),
            'DOGE' => new Dogecoin(),
            'SOL' => new Solana(),
            'BTC' => new Bitcoin(),
            'ETH' => new Ethereum(),
        ];
    }

    public function getAllCurrencies(): array
    {
        return $this->currencies;
    }

    public function getActiveCurrencies(): array
    {
        return array_filter($this->currencies, function($currency) {
            return $currency->isActive();
        });
    }

    public function getCurrency(string $key): ?AbstractCryptoCurrency
    {
        return $this->currencies[$key] ?? null;
    }

    public function getCurrencyBySymbol(string $symbol): ?AbstractCryptoCurrency
    {
        foreach ($this->currencies as $currency) {
            if ($currency->getSymbol() === $symbol) {
                return $currency;
            }
        }
        return null;
    }

    public function getCurrenciesForInvestment(): array
    {
        return array_filter($this->currencies, function($currency) {
            return $currency->allowInvestments() && $currency->isActive();
        });
    }

    public function getCurrenciesForDeposit(): array
    {
        return array_filter($this->currencies, function($currency) {
            return $currency->allowDeposits() && $currency->isActive();
        });
    }

    public function getCurrenciesForWithdrawal(): array
    {
        return array_filter($this->currencies, function($currency) {
            return $currency->allowWithdrawals() && $currency->isActive();
        });
    }

    /**
     * Sync currency definitions to database
     */
    public function syncToDatabase(): void
    {
        foreach ($this->currencies as $key => $currencyService) {
            $data = $currencyService->toArray();
            $data['sort_order'] = $this->getSortOrder($currencyService->getSymbol());

            Currency::updateOrCreate(
                [
                    'symbol' => $currencyService->getSymbol(),
                    'network' => $currencyService->getNetwork()
                ],
                $data
            );
        }
    }

    private function getSortOrder(string $symbol): int
    {
        $order = [
            'USDT' => 1,
            'BTC' => 2,
            'ETH' => 3,
            'SOL' => 4,
            'DOGE' => 5,
        ];

        return $order[$symbol] ?? 99;
    }

    /**
     * Get currency options for form select
     */
    public function getCurrencyOptions(): array
    {
        $options = [];
        foreach ($this->getActiveCurrencies() as $key => $currency) {
            $options[$key] = $currency->getFullName() . ' (' . $currency->getSymbol() . ')';
        }
        return $options;
    }

    /**
     * Format amount according to currency specifications
     */
    public function formatAmount(string $currencyKey, float $amount): string
    {
        $currency = $this->getCurrency($currencyKey);
        if (!$currency) {
            return number_format($amount, 2);
        }
        return $currency->formatAmount($amount);
    }

    /**
     * Validate transaction amount for currency
     */
    public function validateTransactionAmount(string $currencyKey, float $amount): array
    {
        $currency = $this->getCurrency($currencyKey);
        if (!$currency) {
            return ['valid' => false, 'message' => 'Invalid currency'];
        }

        if ($amount < $currency->getMinTransactionAmount()) {
            return [
                'valid' => false,
                'message' => "Minimum amount is {$currency->formatAmount($currency->getMinTransactionAmount())} {$currency->getSymbol()}"
            ];
        }

        $maxAmount = $currency->getMaxTransactionAmount();
        if ($maxAmount !== null && $amount > $maxAmount) {
            return [
                'valid' => false,
                'message' => "Maximum amount is {$currency->formatAmount($maxAmount)} {$currency->getSymbol()}"
            ];
        }

        return ['valid' => true, 'message' => 'Valid amount'];
    }
}