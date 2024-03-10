<?php declare(strict_types=1);

namespace App\Reader;

use App\Dto\Transaction;

interface TransactionReaderInterface
{
    public function readTransaction(): ?Transaction;
}