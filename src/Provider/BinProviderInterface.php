<?php declare(strict_types=1);

namespace App\Provider;

interface BinProviderInterface
{
    public function getCountryCodeByBin(string $bin): string;
}
