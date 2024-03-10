<?php declare(strict_types=1);

namespace Tests\Dto;

use App\Dto\Transaction;
use App\Enum\Currency;
use App\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    #[DataProvider('wrongBinFormatDataProvider')]
    public function testCreateTransactionFailsWhenWrongBinFormat(string $bin): void 
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Bin must contain digits');
 
        new Transaction($bin, 200.00, Currency::EURO);
    }

    public static function wrongBinFormatDataProvider(): array
    {
        return [
            'letters are not allowed' => [
                'bin' => '44400a',
            ],
            'dots are not allowed' => [
                'bin' => '444.00',
            ],
        ];
    }

    public function testCreateTransactionSuccess(): void 
    {
        $transaction = new Transaction('4454', 200.00, Currency::EURO);

        self::assertEquals('4454', $transaction->getBin());
        self::assertEquals(200.00, $transaction->getAmount());
        self::assertEquals(Currency::EURO, $transaction->getCurrency());
    }
}