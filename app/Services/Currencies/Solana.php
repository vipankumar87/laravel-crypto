<?php

namespace App\Services\Currencies;

class Solana extends AbstractCryptoCurrency
{
    public function getName(): string
    {
        return 'Solana';
    }

    public function getSymbol(): string
    {
        return 'SOL';
    }

    public function getNetwork(): string
    {
        return 'SOL';
    }

    public function getIcon(): string
    {
        return '/images/currencies/sol.svg';
    }

    public function getMinTransactionAmount(): float
    {
        return 0.1;
    }

    public function getMaxTransactionAmount(): ?float
    {
        return 10000.0;
    }

    public function getTransactionFee(): float
    {
        return 0.000005; // Very low SOL transaction fee
    }

    public function getWithdrawalFee(): float
    {
        return 0.01; // 0.01 SOL withdrawal fee
    }

    public function getDecimalPlaces(): int
    {
        return 9; // SOL uses 9 decimal places (lamports)
    }

    public function getContractAddress(): ?string
    {
        return null; // Native coin, no contract address
    }

    public function getNetworkConfig(): array
    {
        return [
            'blockchain' => 'Solana',
            'explorer_url' => 'https://explorer.solana.com',
            'rpc_url' => 'https://api.mainnet-beta.solana.com',
            'native_currency' => 'SOL',
            'block_time' => 0.4, // seconds (very fast)
            'confirmations_required' => 32,
            'is_native' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Solana (SOL) - High-performance blockchain with fast transactions and low fees';
    }
}