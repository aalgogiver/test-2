<?php declare(strict_types=1);

namespace Tests\Provider;

use App\Dto\Transaction;
use App\Enum\Currency;
use App\Exception\ExchangeRateProviderException;
use App\Provider\ExchangeRateProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Trait\FixtureTrait;

class ExchangeRateProviderTest extends TestCase
{
    use FixtureTrait;
 
    private ExchangeRateProvider $exchangeRateProvider;

    protected function setUp(): void
    {
        $this->exchangeRateProvider = new ExchangeRateProvider();
    }

    public function testGetExchangeRateWhenFailedToGetData(): void
    {
        self::expectException(ExchangeRateProviderException::class);
        self::expectExceptionMessage('Could not get rates data');
 
        $this->exchangeRateProvider->setUrl($this->getFixtureFilePath('not_existing_url'));

        $this->exchangeRateProvider->getExchangeRate(Currency::USD, Currency::EURO);
    }

    public function testGetExchangeRateWhenWrongJsonFormat(): void
    {
        self::expectException(ExchangeRateProviderException::class);
        self::expectExceptionMessage('Invalid rates data json format');
 
        $this->exchangeRateProvider->setUrl($this->getFixtureFilePath('wrong_json_format'));

        $this->exchangeRateProvider->getExchangeRate(Currency::USD, Currency::EURO);
    }
 
    public function testGetExchangeRateWhenNoRates(): void
    {
        self::expectException(ExchangeRateProviderException::class);
        self::expectExceptionMessage('Invalid rates data format');
 
        $this->exchangeRateProvider->setUrl($this->getFixtureFilePath('no_rates'));

        $this->exchangeRateProvider->getExchangeRate(Currency::USD, Currency::EURO);
    }

    public function testGetExchangeRateWhenNoCurrencyRate(): void
    {
        self::expectException(ExchangeRateProviderException::class);
        self::expectExceptionMessage('No rate data for currency');
 
        $this->exchangeRateProvider->setUrl($this->getFixtureFilePath('no_rate'));

        $this->exchangeRateProvider->getExchangeRate(Currency::USD, Currency::EURO);
    }

    public function testGetExchangeRateWhenSuccessfullyGetsRatesData(): void
    {
        $this->exchangeRateProvider->setUrl($this->getFixtureFilePath('rates'));

        self::assertEquals(2.55, $this->exchangeRateProvider->getExchangeRate(Currency::USD, Currency::EURO));
    }

    private function getFixtureFilePath(string $filename): string
    { 
        return $this->getFixturesPath() . '/ExchangeRateProvider/' . $filename;
    }
}