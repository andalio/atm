<?php

namespace Tests\Unit\Infrastructure\Api;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Api\RatesClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use App\Domain\Commission\Exception\ExchangeRateException;

class RatesClientTest extends TestCase
{
    private HttpClientInterface $httpClient;
    private ResponseInterface $responseMock;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->httpClient->method('request')->willReturn($this->responseMock);
    }

    public function testGetRatesSuccess(): void
    {
        $this->responseMock->method('getContent')
            ->willReturn(json_encode([
                'rates' => [
                    'USD' => 1.1497,
                    'JPY' => 129.53
                ]
            ]));

        $ratesClient = new RatesClient($this->httpClient);
        $rates = $ratesClient->getRates();

        $this->assertArrayHasKey('USD', $rates);
        $this->assertArrayHasKey('JPY', $rates);
        $this->assertEquals(1.1497, $rates['USD']);
        $this->assertEquals(129.53, $rates['JPY']);
    }

    public function testGetRatesThrowsExceptionOnInvalidResponse(): void
    {
        $this->responseMock->method('getContent')->willReturn('{}');

        $this->expectException(ExchangeRateException::class);
        new RatesClient($this->httpClient);
    }

    public function testGetRatesThrowsExceptionOnMalformedJson(): void
    {
        $this->responseMock->method('getContent')->willReturn('{invalid json}');

        $this->expectException(ExchangeRateException::class);
        new RatesClient($this->httpClient);
    }

    public function testGetRatesThrowsExceptionWhenRatesAreMissing(): void
    {
        $this->responseMock->method('getContent')->willReturn(json_encode([]));

        $this->expectException(ExchangeRateException::class);
        new RatesClient($this->httpClient);
    }
}
