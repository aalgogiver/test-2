<?php declare(strict_types=1);

namespace App\Calculator;

use App\Dto\Transaction;

interface CommissionCalculatorInterface
{
    public function getTransactionCommission(Transaction $transaction): float;
}