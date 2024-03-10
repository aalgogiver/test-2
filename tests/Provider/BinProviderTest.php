<?php declare(strict_types=1);

namespace Tests\Provider;

use App\Dto\Transaction;
use App\Exception\BinProviderException;
use App\Provider\BinProvider;
use PHPUnit\Framework\TestCase;
use Tests\Trait\FixtureTrait;

class BinProviderTest extends TestCase
{
    use FixtureTrait;
 
    private BinProvider $binProvider;

    protected function setUp(): void
    {
        $this->binProvider = new BinProvider();
    }

    public function testGetCountryCodeByBinWhenFailedToGetData(): void
    {
        self::expectException(BinProviderException::class);
        self::expectExceptionMessage('Could not get bin data');
 
        $this->binProvider->setBaseUrl($this->getFixtureDirectoryPath());

        $this->binProvider->getCountryCodeByBin('0000000');
    }

    public function testGetCountryCodeByBinWhenWrongJsonFormat(): void
    {
        self::expectException(BinProviderException::class);
        self::expectExceptionMessage('Invalid bin data json format');
 
        $this->binProvider->setBaseUrl($this->getFixtureDirectoryPath());

        $this->binProvider->getCountryCodeByBin('11111111');
    }

    public function testGetCountryCodeByBinWhenSuccessfullyGetsBinData(): void
    {
        $this->binProvider->setBaseUrl($this->getFixtureDirectoryPath());

        self::assertEquals('DK', $this->binProvider->getCountryCodeByBin('45717360'));
    }

    private function getFixtureDirectoryPath(): string
    { 
        return $this->getFixturesPath() . '/BinProvider/';
    }
}