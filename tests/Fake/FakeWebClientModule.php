<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use GuzzleHttp\ClientInterface;
use Override;
use Ray\Di\AbstractModule;

use function dirname;
use function file_get_contents;

/**
 * Overrides the Guzzle client binding with a {@see FakeWebClient} so the
 * module test does not perform live HTTP access.
 */
final class FakeWebClientModule extends AbstractModule
{
    #[Override]
    protected function configure(): void
    {
        $schema = (string) file_get_contents(dirname(__DIR__, 2) . '/docs/web_query.json');
        $this->bind(ClientInterface::class)->toInstance(new FakeWebClient($schema));
    }
}
