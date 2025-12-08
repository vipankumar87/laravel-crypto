<?php

namespace App\Services\Currencies;

class Ethereum extends AbstractCryptoCurrency
{
    public function getName(): string
    {
        return 'Ethereum';
    }

    public function getSymbol(): string
    {
        return 'ETH';
    }

    public function getNetwork(): string
    {
        return 'ETH';
    }

    public function getIcon(): string
    {
        return '/images/currencies/eth.svg';
    }

    public function getMinTransactionAmount(): float
    {
        return 0.001; // 0.001 ETH minimum
    }

    public function getMaxTransactionAmount(): ?float
    {
        return 100.0; // 100 ETH maximum
    }

    public function getTransactionFee(): float
    {
        return 0.002; // 0.002 ETH average gas fee
    }

    public function getWithdrawalFee(): float
    {
        return 0.005; // 0.005 ETH withdrawal fee
    }

    public function getDecimalPlaces(): int
    {
        return 18; // ETH uses 18 decimal places (wei)
    }

    public function getContractAddress(): ?string
    {
        return null; // Native coin, no contract address
    }

    public function getNetworkConfig(): array
    {
        return [
            'blockchain' => 'Ethereum',
            'chain_id' => 1,
            'explorer_url' => 'https://etherscan.io',
            'rpc_url' => 'https://mainnet.infura.io/v3/',
            'native_currency' => 'ETH',
            'block_time' => 12, // seconds
            'confirmations_required' => 12,
            'is_native' => true,
        ];
    }

    public function getDescription(): string
    {
        return 'Ethereum (ETH) - The leading smart contract platform and cryptocurrency';
    }
}