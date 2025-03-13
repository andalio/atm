<?php

namespace App\Domain\Operation;

class Operation
{
    private \DateTime $date;
    private int $userId;
    private string $userType;
    private string $operationType;
    private float $amount;
    private string $currency;

    public function __construct(
        \DateTime $date,
        int $userId,
        string $userType,
        string $operationType,
        float $amount,
        string $currency
    ) {
        $this->date = $date;
        $this->userId = $userId;
        $this->userType = $userType;
        $this->operationType = $operationType;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}