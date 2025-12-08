<?php

namespace App\Services\Currencies;

class Dogecoin extends AbstractCryptoCurrency
{
    public function getName(): string
    {
        return 'Dogecoin';
    }

    public function getSymbol(): string
    {
        return 'DOGE';
    }

    public function getNetwork(): string
    {
        return 'DOGE';
    }

    public function getIcon(): string
    {
        return '/images/currencies/doge.svg';
    }

    public function getMinTransactionAmount(): float
    {
        return 1.0;
    }

    public function getMaxTransactionAmount(): ?float
    {
        return 1000000.0;
    }

    public function getTransactionFee(): float
    {
        return 1.0; // 1 DOGE network fee
    }

    public function getWithdrawalFee(): float
    {
        return 5.0; // 5 DOGE withdrawal fee
    }

    public function getDecimalPlaces(): int
    {
        return 8;
    }

    public function getContractAddress(): ?string
    {
        return null; // Native coin, no contract address
    }

    public function getNetworkConfig(): array
    {
        return [
            'blockchain' => 'Dogecoin',
            'explorer_url' => 'https://dogechain.info',
            'native_currency' => 'DOGE',
            'block_time' => 60, // seconds
            'confirmations_required' => 6,
            'is_native' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Dogecoin (DOGE) - The original meme cryptocurrency with a loyal community';
    }
}