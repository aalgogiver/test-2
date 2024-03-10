<?php declare(strict_types=1);

namespace App\Provider;

use App\Enum\Currency;
use App\Exception\ExchangeRateProviderException;

class ExchangeRateProvider implements ExchangeRateProviderInterface
{
    private string $url = 'https://api.exchangeratesapi.io/latest';

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }
 
    public function getExchangeRate(Currency $fromCurrency, Currency $toCurrency): float
    {
        $jsonData = file_get_contents($this->url);
        if ($jsonData === false) {
            throw new ExchangeRateProviderException('Could not get rates data');
        }
 
        $data = json_decode($jsonData, true);

        if ($data === null) {
            throw new ExchangeRateProviderException(sprintf('Invalid rates data json format "%s"', $jsonData));
        }

        if (!isset($data['rates'])) {
            throw new ExchangeRateProviderException('Invalid rates data format');
        }

        if (!isset($data['rates'][(string)$fromCurrency->value])) {
            throw new ExchangeRateProviderException(sprintf('No rate data for currency "%s"', (string)$fromCurrency->value));
        }
 
        return (float)$data['rates'][(string)$fromCurrency->value];
    }
}
