<?php declare(strict_types=1);

namespace App\Command;

use App\Calculator\CommissionCalculatorInterface;
use App\Factory\TransactionFileReaderFactoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class CommissionCalculationCommand extends Command
{
    public function __construct(
        private readonly CommissionCalculatorInterface $commissionCalculator,
        private readonly TransactionFileReaderFactoryInterface $fileReaderFactory,
    ) {
        parent::__construct();
    }
 
    protected function configure(): void
    {
        $this
            ->setName('commission_calculator')
            ->addArgument('filename', InputArgument::REQUIRED, 'Transactions file name');
    }
 
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $reader = $this->fileReaderFactory->createReader($input->getArgument('filename'));

            while ($transaction = $reader->readTransaction()) {
                $commission = $this->commissionCalculator->getTransactionCommission($transaction);

                $output->writeln((string)$commission);
            }

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $output->writeln('Failed to calculate commissions.');
            $output->writeln($e->getMessage());
 
            return Command::FAILURE;
        }
    } 
}