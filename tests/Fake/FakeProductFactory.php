<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

/**
 * Instance factory resolved via DI injector.
 *
 * Demonstrates BDR: the factory can hold constructor-injected services
 * (FakeTaxCalculator) and apply business logic when building the entity.
 */
final class FakeProductFactory
{
    public function __construct(
        private FakeTaxCalculator $tax,
    ) {
    }

    public function factory(string $name, int $price): FakeProductEntity
    {
        return new FakeProductEntity($name, $this->tax->applyTax($price));
    }
}
