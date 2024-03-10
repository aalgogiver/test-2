<?php declare(strict_types=1);

namespace Tests\Calculator;

use App\Calculator\CeilingCommissionCalculatorDecorator;
use App\Calculator\CommissionCalculatorInterface;
use App\Dto\Transaction;
use App\Provider\ExchangeRateProviderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CeilingCommissionCalculatorDecoratorTest extends TestCase
{
    private CommissionCalculatorInterface $commissionCalculator;
    private CeilingCommissionCalculatorDecorator $decorator;

    protected function setUp(): void
    {
        $this->commissionCalculator = $this->createMock(CommissionCalculatorInterface::class);
        $this->decorator = new CeilingCommissionCalculatorDecorator($this->commissionCalculator);
    }

    #[DataProvider('getTransactionCommissionDataProvider')]
    public function testGetTransactionCommission(float $amount, float $expectedAmount): void
    {
        $transaction = $this->createMock(Transaction::class);

        $this->commissionCalculator
            ->expects(self::once())
            ->method('getTransactionCommission')
            ->with($transaction)
            ->willReturn($amount);

        self::assertEquals($expectedAmount, $this->decorator->getTransactionCommission($transaction));
    }

    public static function getTransactionCommissionDataProvider(): array
    {
        return [
            'does not add ceiling #1' => [
                'amount' => 1.00,
                'expectedAmount' => 1.00,
            ],
            'does not add ceiling #2' => [
                'amount' => 1.07,
                'expectedAmount' => 1.07,
            ],
            'does not add ceiling #3' => [
                'amount' => 1.05000000000,
                'expectedAmount' => 1.05,
            ],
            'adds ceiling when significant digits after third digit #1' => [
                'amount' => 0.46180,
                'expectedAmount' => 0.47,
            ],
            'adds ceiling when significant digits after third digit #2' => [
                'amount' => 0.46000001,
                'expectedAmount' => 0.47,
            ],
        ];
    }
}