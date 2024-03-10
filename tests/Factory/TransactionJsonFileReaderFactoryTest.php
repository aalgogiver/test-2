<?php declare(strict_types=1);

namespace Tests\Factory;

use App\Reader\TransactionJsonFileReader;
use App\Factory\TransactionJsonFileReaderFactory;
use PHPUnit\Framework\TestCase;

class TransactionJsonFileReaderFactoryTest extends TestCase
{
    public function testCreateReader(): void 
    {
        $factory = new TransactionJsonFileReaderFactory();

        self::assertInstanceOf(TransactionJsonFileReader::class, $factory->createReader('filename.txt'));
    }
}