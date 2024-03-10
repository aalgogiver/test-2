<?php declare(strict_types=1);

namespace App\Calculator;

use App\Calculator\CommissionCalculatorInterface;
use App\Dto\Transaction;

class CeilingCommissionCalculatorDecorator implements CommissionCalculatorInterface
{
    public function __construct(private readonly CommissionCalculatorInterface $commissionCalculator)
    {
    }

    public function getTransactionCommission(Transaction $transaction): float
    {
        $commissionAmount = $this->commissionCalculator->getTransactionCommission($transaction);

        return ceil($commissionAmount * 100) / 100;
    }
}