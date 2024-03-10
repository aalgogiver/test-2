<?php declare(strict_types=1);

namespace Tests\Calculator;

use App\Calculator\CommissionCalculator;
use App\Dto\Transaction;
use App\Enum\Currency;
use App\Exception\BinProviderException;
use App\Exception\CommissionCalculatorException;
use App\Exception\ExchangeRateProviderException;
use App\Provider\BinProviderInterface;
use App\Provider\ExchangeRateProviderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    private BinProviderInterface $binProviderMock;
    private ExchangeRateProviderInterface $exchangeRateProviderMock;
    private CommissionCalculator $commissionCalculator;

    protected function setUp(): void
    {
        $this->binProviderMock = $this->createMock(BinProviderInterface::class);
        $this->exchangeRateProviderMock = $this->createMock(ExchangeRateProviderInterface::class);
        $this->commissionCalculator = new CommissionCalculator($this->binProviderMock, $this->exchangeRateProviderMock);
    }

    public function testGetTransactionCommissionWhenBinProviderFails(): void
    {
        self::expectException(CommissionCalculatorException::class);
 
        $this->binProviderMock
            ->expects(self::any())
            ->method('getCountryCodeByBin')
            ->willThrowException(new BinProviderException());

        $this->commissionCalculator->getTransactionCommission(new Transaction('44400', 100.00, Currency::EURO));
    }

    public function testGetTransactionCommissionWhenExchangeRateProviderFails(): void
    {
        self::expectException(CommissionCalculatorException::class);

        $this->exchangeRateProviderMock
            ->expects(self::any())
            ->method('getExchangeRate')
            ->willThrowException(new ExchangeRateProviderException());
 
        $this->commissionCalculator->getTransactionCommission(new Transaction('44400', 100.00, Currency::USD));
    }

    #[DataProvider('getTransactionCommissionForEuroCurrencyDataProvider')]
    public function testGetTransactionCommissionCalculateCommissionForEuroCurrency(
        Transaction $transaction,
        string $countryCode,
        float $expectedCommissionInEuro
    ): void {
        $this->exchangeRateProviderMock
            ->expects(self::never())
            ->method('getExchangeRate');

        $this->binProviderMock
            ->expects(self::once())
            ->method('getCountryCodeByBin')
            ->willReturn($countryCode);

        self::assertEquals($expectedCommissionInEuro, $this->commissionCalculator->getTransactionCommission($transaction));
    }

    public static function getTransactionCommissionForEuroCurrencyDataProvider(): array
    {
        return [
            'european country commission is 1 percent' => [
                'transaction' => new Transaction('444005', 100.00, Currency::EURO),
                'countryCode' => 'AT',
                'expectedCommissionInEuro' => 1.00,
            ],
            'non european country commission is 2 percent' => [
                'transaction' => new Transaction('444005', 100.00, Currency::EURO),
                'countryCode' => 'AU',
                'expectedCommissionInEuro' => 2.00,
            ],
        ];
    }

    #[DataProvider('getTransactionCommissionForNonEuroCurrencyDataProvider')]
    public function testGetTransactionCommissionForNonEuroCurrency(
        Transaction $transaction,
        string $countryCode,
        float $exchangeRate,
        float $expectedCommissionInEuro
    ): void {
        $this->exchangeRateProviderMock
            ->expects(self::once())
            ->method('getExchangeRate')
            ->with($transaction->getCurrency(), Currency::EURO)
            ->willReturn($exchangeRate);

        $this->binProviderMock
            ->expects(self::once())
            ->method('getCountryCodeByBin')
            ->willReturn($countryCode);

        self::assertEquals($expectedCommissionInEuro, $this->commissionCalculator->getTransactionCommission($transaction));
    }

    public static function getTransactionCommissionForNonEuroCurrencyDataProvider(): array
    {
        return [
            'european country commission is 1 percent' => [
                'transaction' => new Transaction('444005', 200.00, Currency::USD),
                'countryCode' => 'AT',
                'exchangeRate' => 2,
                'expectedCommissionInEuro' => 1.00,
            ],
            'non european country commission is 2 percent' => [
                'transaction' => new Transaction('444005', 100.00, Currency::USD),
                'countryCode' => 'AU',
                'exchangeRate' => 1,
                'expectedCommissionInEuro' => 2.00,
            ],
            'non european country with zero exchange rate' => [
                'transaction' => new Transaction('444005', 100.00, Currency::USD),
                'countryCode' => 'AU',
                'exchangeRate' => 0.00,
                'expectedCommissionInEuro' => 2.00,
            ],
        ];
    }
}