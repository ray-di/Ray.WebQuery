<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

final class FakeStaticProductFactory
{
    public static function factory(string $name, int $price): FakeProductEntity
    {
        return new FakeProductEntity($name, $price);
    }
}
