<?php declare(strict_types=1);

namespace Tests\Command;

use App\Calculator\CommissionCalculatorInterface;
use App\Command\CommissionCalculationCommand;
use App\Dto\Transaction;
use App\Exception\BinProviderException;
use App\Exception\ReaderException;
use App\Factory\TransactionFileReaderFactoryInterface;
use App\Reader\TransactionReaderInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class CommissionCalculationCommandTest extends TestCase
{
    private CommissionCalculatorInterface $commissionCalculator;
    private TransactionFileReaderFactoryInterface $transactionFileReaderFactory;
    private TransactionReaderInterface $transactionReader;
    private CommissionCalculationCommand $command;

    protected function setUp(): void
    {
        $this->commissionCalculator = $this->createMock(CommissionCalculatorInterface::class);
        $this->transactionFileReaderFactory = $this->createMock(TransactionFileReaderFactoryInterface::class);
        $this->command = new CommissionCalculationCommand($this->commissionCalculator, $this->transactionFileReaderFactory);
    }

    public function testCommandFailsWhenMissingFilenameArgument(): void
    {
        self::expectException(RuntimeException::class);
 
        $commandTester = new CommandTester($this->command);
        $commandTester->execute([]);
    }

    public function testCommandWhenNoTransactions(): void
    {
        $this->configureTransactionReader([]);

        $this->commissionCalculator
            ->expects(self::never())
            ->method('getTransactionCommission');
 
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['filename' => 'filename.txt']);

        $output = $commandTester->getDisplay();
 
        self::assertEmpty($output);
    }

    public function testCommandWithTransactions(): void
    {
        $firstTransaction = $this->createMock(Transaction::class);
        $secondTransaction = $this->createMock(Transaction::class);
        $this->configureTransactionReader([$firstTransaction, $secondTransaction]);

        $matcher = self::exactly(2);
        $this->commissionCalculator
            ->expects($matcher)
            ->method('getTransactionCommission')
            ->willReturnCallback(function ($transaction) use ($matcher, $firstTransaction, $secondTransaction) {
                match ($matcher->numberOfInvocations()) {
                    1 => self::assertSame($firstTransaction, $transaction),
                    2 => self::assertSame($secondTransaction, $transaction),
                };

                return match ($matcher->numberOfInvocations()) {
                    1 => 1.65,
                    2 => 2.00,
                };
            });
 
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['filename' => 'filename.txt']);

        $output = $commandTester->getDisplay();
 
        self::assertEquals("1.65\n2\n", $output);
    }

    public function testCommandWhenReaderException(): void
    {
        $transactionReader = $this->createMock(TransactionReaderInterface::class);
        $transactionReader
            ->expects(self::any())
            ->method('readTransaction')
            ->willThrowException(new ReaderException());

        $this->configureTransactionReaderFactory($transactionReader);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['filename' => 'filename.txt']);

        self::assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    public function testCommandWhenCommissionCalculatorException(): void
    {
        $transaction = $this->createMock(Transaction::class);
        $this->configureTransactionReader([$transaction]);

        $this->commissionCalculator
            ->expects(self::any())
            ->method('getTransactionCommission')
            ->willThrowException(new BinProviderException());

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['filename' => 'filename.txt']);

        self::assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }

    private function configureTransactionReader(array $transactions): void
    {
        $transactions[] = null;
        $transactionReader = $this->createMock(TransactionReaderInterface::class);

        $transactionReader
            ->expects(self::any())
            ->method('readTransaction')
            ->willReturnOnConsecutiveCalls(...$transactions);

        $this->configureTransactionReaderFactory($transactionReader);
    }

    private function configureTransactionReaderFactory(TransactionReaderInterface $transactionReader): void
    {
        $this->transactionFileReaderFactory
            ->expects(self::any())
            ->method('createReader')
            ->willReturn($transactionReader);
    }
}