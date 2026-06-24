<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

final class FakeTaxCalculator
{
    public function applyTax(int $price): int
    {
        return (int) ($price * 1.1);
    }
}
