<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

/**
 * Entity with no constructor — used to exercise WebFetchClass (property-set path).
 */
final class FakeNoCtorEntity
{
    public string $name = '';
    public int $price = 0;
}
