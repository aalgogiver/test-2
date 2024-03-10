<?php declare(strict_types=1);

namespace App\Factory;

use App\Reader\TransactionReaderInterface;

interface TransactionFileReaderFactoryInterface
{
    public function createReader(string $filename): TransactionReaderInterface;
}