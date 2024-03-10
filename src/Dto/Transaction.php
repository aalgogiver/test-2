<?php declare(strict_types=1);

namespace App\Dto;

use App\Enum\Currency;
use App\Exception\InvalidArgumentException;

class Transaction
{
    private const string BIN_PATTERN = '/^\d+$/';
 
    public function __construct(
        private readonly string $bin,
        private readonly float $amount,
        private readonly Currency $currency
    ) {
        if (!preg_match(self::BIN_PATTERN, $bin)) {
            throw new InvalidArgumentException('Bin must contain digits');
        }
    }

    public function getBin(): string
    {
        return $this->bin;
    }
 
    public function getAmount(): float
    {
        return $this->amount;
    }
 
    public function getCurrency(): Currency
    {
        return $this->currency;
    }
}