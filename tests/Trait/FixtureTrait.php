<?php declare(strict_types=1);

namespace Tests\Trait;

trait FixtureTrait
{
    private function getFixturesPath(): string
    {
        return __DIR__ . '/../Fixtures';
    }
}