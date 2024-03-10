<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Calculator\CeilingCommissionCalculatorDecorator;
use App\Calculator\CommissionCalculator;
use App\Command\CommissionCalculationCommand;
use App\Factory\TransactionJsonFileReaderFactory;
use App\Provider\BinProvider;
use App\Provider\ExchangeRateProvider;
use Symfony\Component\Console\Application;

$commissionCalculator = new CommissionCalculator(new BinProvider(), new ExchangeRateProvider());
$command = new CommissionCalculationCommand(
    new CeilingCommissionCalculatorDecorator($commissionCalculator),
    new TransactionJsonFileReaderFactory()
);

$application = new Application();
$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();