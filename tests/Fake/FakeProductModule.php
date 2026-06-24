<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use GuzzleHttp\ClientInterface;
use Override;
use Ray\Di\AbstractModule;

/**
 * Overrides the Guzzle client for BDR / entity / PostFetch integration tests.
 *
 * The default body is a single-product JSON object used for row tests.
 * Pass a custom JSON body to the constructor for row_list or other scenarios.
 */
final class FakeProductModule extends AbstractModule
{
    public function __construct(private string $body)
    {
    }

    #[Override]
    protected function configure(): void
    {
        $this->bind(ClientInterface::class)->toInstance(new FakeWebClient($this->body));
        $this->bind(FakeTaxCalculator::class)->toInstance(new FakeTaxCalculator());
        $this->bind(FakeProductFactory::class)->toInstance(
            new FakeProductFactory(new FakeTaxCalculator()),
        );
    }
}
