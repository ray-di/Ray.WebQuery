<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

final class FakeProductEntity
{
    public function __construct(
        public readonly string $name,
        public readonly int $price,
    ) {
    }
}
