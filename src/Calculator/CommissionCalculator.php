<?php declare(strict_types=1);

namespace App\Calculator;

use App\Calculator\CommissionCalculatorInterface;
use App\Dto\Transaction;
use App\Enum\Currency;
use App\Enum\EuropeanCountryCode;
use App\Exception\CommissionCalculatorException;
use App\Provider\BinProviderInterface;
use App\Provider\ExchangeRateProviderInterface;
use Throwable;

class CommissionCalculator implements CommissionCalculatorInterface
{
    private const int COMMISION_PERCENTAGE_EUROPE = 1; 
    private const int COMMISION_PERCENTAGE_WORLD = 2; 

    public function __construct(
        private readonly BinProviderInterface $binProvider,
        private readonly ExchangeRateProviderInterface $exchangeRateProvider
    ) {
    }

    public function getTransactionCommission(Transaction $transaction): float
    {
        try {
            $euroAmount = $this->getTransactionAmountInEuro($transaction);
    
            return $euroAmount * $this->getCommissionPercentageByBin($transaction->getBin()) / 100;
        } catch (Throwable $exception) {
            throw new CommissionCalculatorException(
                message: sprintf('Could not calculate commission: %s', $exception->getMessage()),
                previous: $exception
            );
        }
    }

    private function getTransactionAmountInEuro(Transaction $transaction): float
    {
        if ($transaction->getCurrency() === Currency::EURO) {
            return $transaction->getAmount();
        }

        $rate = $this->getExchangeRate($transaction->getCurrency());

        return $rate !== 0.00 ? $transaction->getAmount() / $rate : $transaction->getAmount();
    }

    private function getExchangeRate(Currency $currency): float
    {
        return $this->exchangeRateProvider->getExchangeRate($currency, Currency::EURO);
    }

    private function getCommissionPercentageByBin(string $bin): int
    {
        $countryCode = $this->binProvider->getCountryCodeByBin($bin);
 
        return $this->getCommissionPercentageByCountryCode($countryCode);
    }

    private function getCommissionPercentageByCountryCode(string $countryCode): int
    {
        if (EuropeanCountryCode::tryFrom($countryCode) === null) {
            return self::COMMISION_PERCENTAGE_WORLD;
        }
 
        return self::COMMISION_PERCENTAGE_EUROPE;
    }
}