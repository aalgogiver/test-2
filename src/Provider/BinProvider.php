<?php declare(strict_types=1);

namespace App\Provider;

use App\Exception\BinProviderException;

class BinProvider implements BinProviderInterface
{
    private string $baseUrl = 'https://lookup.binlist.net';

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function getCountryCodeByBin(string $bin): string
    {
        $url = sprintf('%s/%s', $this->baseUrl, $bin);
 
        $jsonData = file_get_contents($url);
        if ($jsonData === false) {
            throw new BinProviderException('Could not get bin data');
        }
 
        $data = json_decode($jsonData);

        if ($data === null) {
            throw new BinProviderException(sprintf('Invalid bin data json format "%s"', $jsonData));
        }
 
        return $data->country->alpha2;
    }
}
