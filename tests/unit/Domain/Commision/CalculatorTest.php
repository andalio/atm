<?php

namespace App\Tests\unit\Domain\Commision;

use PHPUnit\Framework\TestCase;
use App\Domain\Commission\Calculator;
use App\Domain\Operation\Operation;
use App\Infrastructure\Api\RatesClient;

class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    protected function setUp(): void
    {
        $ratesClient = $this->createMock(RatesClient::class);

        $ratesClient->method('convertToEur')
            ->willReturnCallback(fn ($amount, $currency) => match ($currency) {
                'USD' => $amount / 1.1497,
                'JPY' => $amount / 129.53,
                default => $amount,
            });

        $ratesClient->method('convertFromEur')
            ->willReturnCallback(fn ($amount, $currency) => match ($currency) {
                'USD' => $amount * 1.1497,
                'JPY' => $amount * 129.53,
                default => $amount,
            });

        $this->calculator = new Calculator($ratesClient);
    }

    public function testDepositCommission(): void
    {
        $operation = new Operation(new \DateTime('2025-03-10'), 1, 'private', 'deposit', 200.00, 'EUR');
        $this->assertEquals(0.06, $this->calculator->calculate($operation));
    }

    public function testWithdrawBusinessCommission(): void
    {
        $operation = new Operation(new \DateTime('2025-03-10'), 2, 'business', 'withdraw', 300.00, 'EUR');
        $this->assertEquals(1.50, $this->calculator->calculate($operation));
    }

    public function testWithdrawPrivateFreeLimit(): void
    {
        $operation1 = new Operation(new \DateTime('2015-01-01'), 1, 'private', 'withdraw', 1000.00, 'EUR');
        $operation2 = new Operation(new \DateTime('2015-01-02'), 1, 'private', 'withdraw', 1000.00, 'EUR');
        $operation3 = new Operation(new \DateTime('2015-01-03'), 1, 'private', 'withdraw', 1000.00, 'EUR');
        $operation4 = new Operation(new \DateTime('2015-01-04'), 1, 'private', 'withdraw', 500.00, 'EUR');

        $this->assertEquals(0.00, $this->calculator->calculate($operation1));
        $this->assertEquals(3.00, $this->calculator->calculate($operation2));
        $this->assertEquals(3.00, $this->calculator->calculate($operation3));
        $this->assertEquals(1.50, $this->calculator->calculate($operation4));
    }

    public function testWithdrawJPY(): void
    {
        $operation = new Operation(new \DateTime('2025-03-10'), 5, 'private', 'withdraw', 3000000, 'JPY');
        $this->assertEquals(8612, $this->calculator->calculate($operation));
    }
}