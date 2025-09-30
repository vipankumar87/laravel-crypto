<?php

namespace App\Services\Currencies;

class BEP20USDT extends AbstractCryptoCurrency
{
    public function getName(): string
    {
        return 'Tether USD';
    }

    public function getSymbol(): string
    {
        return 'USDT';
    }

    public function getNetwork(): string
    {
        return 'BEP20';
    }

    public function getIcon(): string
    {
        return '/images/currencies/usdt.svg';
    }

    public function getMinTransactionAmount(): float
    {
        return 1.0;
    }

    public function getMaxTransactionAmount(): ?float
    {
        return 100000.0;
    }

    public function getTransactionFee(): float
    {
        return 1.0; // 1 USDT processing fee
    }

    public function getWithdrawalFee(): float
    {
        return 2.0; // 2 USDT withdrawal fee
    }

    public function getDecimalPlaces(): int
    {
        return 2; // USDT typically uses 2 decimal places
    }

    public function getContractAddress(): ?string
    {
        return '0x55d398326f99059fF775485246999027B3197955'; // BSC USDT contract
    }

    public function getNetworkConfig(): array
    {
        return [
            'blockchain' => 'Binance Smart Chain',
            'chain_id' => 56,
            'explorer_url' => 'https://bscscan.com',
            'rpc_url' => 'https://bsc-dataseed1.binance.org/',
            'native_currency' => 'BNB',
            'block_time' => 3, // seconds
            'confirmations_required' => 12,
        ];
    }

    public function getDescription(): string
    {
        return 'Tether USD (USDT) on Binance Smart Chain (BEP20) - A stable cryptocurrency pegged to the US Dollar';
    }
}