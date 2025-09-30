<?php

namespace App\Services\Currencies;

abstract class AbstractCryptoCurrency
{
    abstract public function getName(): string;
    abstract public function getSymbol(): string;
    abstract public function getNetwork(): string;
    abstract public function getIcon(): string;
    abstract public function getMinTransactionAmount(): float;
    abstract public function getMaxTransactionAmount(): ?float;
    abstract public function getTransactionFee(): float;
    abstract public function getWithdrawalFee(): float;
    abstract public function getDecimalPlaces(): int;
    abstract public function getContractAddress(): ?string;
    abstract public function getNetworkConfig(): array;

    public function getFullName(): string
    {
        return $this->getNetwork() . ' ' . $this->getName();
    }

    public function getTransactionFeeType(): string
    {
        return 'fixed'; // Most crypto transactions use fixed fees
    }

    public function isActive(): bool
    {
        return true;
    }

    public function allowDeposits(): bool
    {
        return true;
    }

    public function allowWithdrawals(): bool
    {
        return true;
    }

    public function allowInvestments(): bool
    {
        return true;
    }

    public function getDescription(): string
    {
        return "Cryptocurrency: {$this->getName()} on {$this->getNetwork()} network";
    }

    public function formatAmount(float $amount): string
    {
        return number_format($amount, $this->getDecimalPlaces(), '.', '');
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'symbol' => $this->getSymbol(),
            'network' => $this->getNetwork(),
            'full_name' => $this->getFullName(),
            'icon' => $this->getIcon(),
            'min_transaction_amount' => $this->getMinTransactionAmount(),
            'max_transaction_amount' => $this->getMaxTransactionAmount(),
            'transaction_fee' => $this->getTransactionFee(),
            'transaction_fee_type' => $this->getTransactionFeeType(),
            'withdrawal_fee' => $this->getWithdrawalFee(),
            'is_active' => $this->isActive(),
            'allow_deposits' => $this->allowDeposits(),
            'allow_withdrawals' => $this->allowWithdrawals(),
            'allow_investments' => $this->allowInvestments(),
            'decimal_places' => $this->getDecimalPlaces(),
            'description' => $this->getDescription(),
            'contract_address' => $this->getContractAddress(),
            'network_config' => $this->getNetworkConfig(),
        ];
    }
}