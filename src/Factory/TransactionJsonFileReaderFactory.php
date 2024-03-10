<?php declare(strict_types=1);

namespace App\Factory;

use App\Factory\TransactionFileReaderFactoryInterface;
use App\Reader\TransactionJsonFileReader;
use App\Reader\TransactionReaderInterface;

class TransactionJsonFileReaderFactory implements TransactionFileReaderFactoryInterface
{
    public function createReader(string $filename): TransactionReaderInterface
    {
        return new TransactionJsonFileReader($filename);
    }
}