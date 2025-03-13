<?php

namespace App\Domain\Commission;

use App\Domain\Commission\Exception\InvalidOperationTypeException;
use App\Domain\Operation\Operation;
use App\Infrastructure\Api\RatesClient;

class Calculator
{
    const DEPOSIT_FEE = 0.0003;
    const WITHDRAW_PRIVATE_FEE = 0.003;
    const WITHDRAW_BUSINESS_FEE = 0.005;

    private const PRIVATE_WITHDRAW_FREE_LIMIT = 1000.00;
    private const PRIVATE_WITHDRAW_FREE_OPERATIONS = 3;

    private const DEPOSIT = 'deposit';
    private const WITHDRAW = 'withdraw';

    private const PRIVATE_USER = 'private';

    private array $weeklyWithdrawals = [];

    public function __construct(
        private readonly RatesClient $ratesClient
    ) {
    }

    public function calculate(Operation $operation): float
    {
        $commissionInEur = match ($operation->getOperationType()) {
            self::DEPOSIT => $this->calculateDepositCommission($operation),
            self::WITHDRAW => $this->calculateWithdrawCommission($operation),
            default => throw new InvalidOperationTypeException(),
        };

        $commissionInOriginalCurrency = $this->ratesClient->convertFromEur($commissionInEur, $operation->getCurrency());

        return $this->roundUp($commissionInOriginalCurrency, $operation->getCurrency());
    }

    private function calculateDepositCommission(Operation $operation): float
    {
        return $operation->getAmount() * self::DEPOSIT_FEE;
    }

    private function calculateWithdrawCommission(Operation $operation): float
    {
        if ($operation->getUserType() === self::PRIVATE_USER) {
            return $this->calculatePrivateWithdrawCommission($operation);
        }

        return $operation->getAmount() * self::WITHDRAW_BUSINESS_FEE;
    }

    private function calculatePrivateWithdrawCommission(Operation $operation): float
    {
        $weekKey = $operation->getDate()->format('oW');
        $userId = $operation->getUserId();

        $amountInEur = $this->ratesClient->convertToEur($operation->getAmount(), $operation->getCurrency());

        if (!isset($this->weeklyWithdrawals[$userId][$weekKey])) {
            $this->weeklyWithdrawals[$userId][$weekKey] = [
                'count' => 0,
                'total' => 0.00
            ];
        }

        $withdrawData = &$this->weeklyWithdrawals[$userId][$weekKey];

        if ($withdrawData['count'] < self::PRIVATE_WITHDRAW_FREE_OPERATIONS) {
            $remainingFreeLimit = self::PRIVATE_WITHDRAW_FREE_LIMIT - $withdrawData['total'];
            $withdrawData['count']++;

            if ($amountInEur <= $remainingFreeLimit) {
                $withdrawData['total'] += $amountInEur;
                return 0.00;
            }

            $taxableAmount = $amountInEur - $remainingFreeLimit;
            $withdrawData['total'] = self::PRIVATE_WITHDRAW_FREE_LIMIT;
        } else {
            $taxableAmount = $amountInEur;
            $withdrawData['total'] += $amountInEur;
        }

        return $taxableAmount * self::WITHDRAW_PRIVATE_FEE;
    }

    private function roundUp(float $amount, string $currency): float
    {
        $decimalPlaces = match ($currency) {
            'JPY' => 0,
            default => 2,
        };

        return ceil($amount * (10 ** $decimalPlaces)) / (10 ** $decimalPlaces);
    }
}
