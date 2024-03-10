<?php declare(strict_types=1);

namespace App\Provider;

use App\Enum\Currency;

interface ExchangeRateProviderInterface
{
    public function getExchangeRate(Currency $fromCurrency, Currency $toCurrency): float;
}
