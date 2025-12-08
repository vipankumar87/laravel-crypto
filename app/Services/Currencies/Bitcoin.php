<?php

namespace App\Services\Currencies;

class Bitcoin extends AbstractCryptoCurrency
{
    public function getName(): string
    {
        return 'Bitcoin';
    }

    public function getSymbol(): string
    {
        return 'BTC';
    }

    public function getNetwork(): string
    {
        return 'BTC';
    }

    public function getIcon(): string
    {
        return '/images/currencies/btc.svg';
    }

    public function getMinTransactionAmount(): float
    {
        return 0.0001; // 0.0001 BTC minimum
    }

    public function getMaxTransactionAmount(): ?float
    {
        return 10.0; // 10 BTC maximum
    }

    public function getTransactionFee(): float
    {
        return 0.0005; // 0.0005 BTC network fee
    }

    public function getWithdrawalFee(): float
    {
        return 0.001; // 0.001 BTC withdrawal fee
    }

    public function getDecimalPlaces(): int
    {
        return 8; // Bitcoin uses 8 decimal places (satoshis)
    }

    public function getContractAddress(): ?string
    {
        return null; // Native coin, no contract address
    }

    public function getNetworkConfig(): array
    {
        return [
            'blockchain' => 'Bitcoin',
            'explorer_url' => 'https://blockstream.info',
            'native_currency' => 'BTC',
            'block_time' => 600, // 10 minutes
            'confirmations_required' => 6,
            'is_native' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Bitcoin (BTC) - The original and most trusted cryptocurrency';
    }
}