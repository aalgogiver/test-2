<?php declare(strict_types=1);

namespace App\Reader;

use App\Dto\Transaction;
use App\Enum\Currency;
use App\Exception\InvalidArgumentException;
use App\Exception\ReaderException;

class TransactionJsonFileReader implements TransactionReaderInterface
{
    private $file = null;
 
    public function __construct(private readonly string $filepath)
    {
    }

    public function readTransaction(): ?Transaction
    {
        $line = fgets($this->getFile());

        if ($line !== false) {
            return $this->mapLineToTransaction($line);
        }

        return null;
    }

    private function mapLineToTransaction(string $line): Transaction
    {
        $data = json_decode($line, true);

        if ($data === null) {
            throw new ReaderException(sprintf('Invalid transaction format "%s"', $line));
        }

        if (!isset($data['bin'], $data['amount'], $data['currency'])) {
            throw new ReaderException(sprintf('Bad transaction data "%s", it must contain \'bin\', \'amount\' and \'currency\' fields.', $line));
        }
 
        if (Currency::tryFrom($data['currency']) === null) {
            throw new InvalidArgumentException('Wrong currency code');
        }
 
        return new Transaction($data['bin'], (float)$data['amount'], Currency::from($data['currency']));
    }

    private function getFile(): mixed
    {
        if ($this->file !== null) {
            return $this->file;
        }

        if (!file_exists($this->filepath)) {
            throw new ReaderException(sprintf('Could not find file "%s"', $this->filepath));
        }
        
        $file = fopen($this->filepath, 'r');

        if ($file === false) {
            throw new ReaderException(sprintf('Could not open file "%s"', $this->filepath));
        }

        $this->file = $file;

        return $this->file;
    }
}