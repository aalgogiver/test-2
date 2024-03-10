<?php declare(strict_types=1);

namespace Tests\Reader;

use App\Dto\Transaction;
use App\Enum\Currency;
use App\Exception\InvalidArgumentException;
use App\Exception\ReaderException;
use App\Reader\TransactionJsonFileReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Trait\FixtureTrait;

class TransactionJsonFileReaderTest extends TestCase
{
    use FixtureTrait;
 
    public function testThrowsExceptionWhenFileDoesNotExists(): void
    {
        self::expectException(ReaderException::class);
        self::expectExceptionMessage('Could not find file');
 
        $reader = new TransactionJsonFileReader($this->getFixtureFilePath('not_existing_transactions_file.txt'));

        $reader->readTransaction();
    }

    public function testReturnsNoTransactionDataWhenEmptyFile(): void
    {
        $reader = new TransactionJsonFileReader($this->getFixtureFilePath('empty.txt'));

        self::assertNull($reader->readTransaction());
    }
 
    public function testThrowsExceptionWhenBadJsonFormat(): void
    {
        self::expectException(ReaderException::class);
        self::expectExceptionMessage('Invalid transaction format');
 
        $reader = new TransactionJsonFileReader($this->getFixtureFilePath('bad_json_format.txt'));

        $reader->readTransaction();
    }
 
    #[DataProvider('missingTransactionDataDataProvider')]
    public function testThrowsExceptionWhenMissingTransactionData(
        string $fixtureFile,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        self::expectException($exceptionClass);
        self::expectExceptionMessage($exceptionMessage);
 
        $reader = new TransactionJsonFileReader($this->getFixtureFilePath($fixtureFile));

        $reader->readTransaction();
    }

    public static function missingTransactionDataDataProvider(): array
    {
        return [
            'missing bin field' => [
                'fixtureFile' => 'missing_bin_field.txt',
                'exceptionClass' => ReaderException::class,
                'exceptionMessage' => 'Bad transaction data',
            ],
            'missing amount field' => [
                'fixtureFile' => 'missing_amount_field.txt',
                'exceptionClass' => ReaderException::class,
                'exceptionMessage' => 'Bad transaction data',
            ],
            'missing currency field' => [
                'fixtureFile' => 'missing_currency_field.txt',
                'exceptionClass' => ReaderException::class,
                'exceptionMessage' => 'Bad transaction data',
            ],
            'bad currency code' => [
                'fixtureFile' => 'bad_currency_code.txt',
                'exceptionClass' => InvalidArgumentException::class,
                'exceptionMessage' => 'Wrong currency code',
            ],
        ];
    }

    public function testReturnsOneTransactionData(): void
    {
        $reader = new TransactionJsonFileReader($this->getFixtureFilePath('one_transaction.txt'));
 
        self::assertEquals(new Transaction('45717360', 100.00, Currency::EURO), $reader->readTransaction());

        self::assertNull($reader->readTransaction());
    }

    public function testReturnsSeveralTransactionData(): void
    {
        $reader = new TransactionJsonFileReader($this->getFixtureFilePath('several_transactions.txt'));
 
        self::assertEquals(new Transaction('45717360', 100.00, Currency::EURO), $reader->readTransaction());
        self::assertEquals(new Transaction('45817360', 200.00, Currency::USD), $reader->readTransaction());
        self::assertEquals(new Transaction('45917360', 300.00, Currency::EURO), $reader->readTransaction());

        self::assertNull($reader->readTransaction());
    }
 
    private function getFixtureFilePath(string $filename): string
    {
        return $this->getFixturesPath() . '/Reader/' . $filename;
    }
}