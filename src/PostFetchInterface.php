<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

/**
 * Implemented by return-type classes that wish to post-process fetch results.
 *
 * The static named constructor receives all context produced by the fetch layer
 * and returns a fully formed domain object — no DI involved.
 *
 * Analogous to PostQueryInterface in ray/media-query, but for the Web layer
 * where a single HTTP request replaces multiple SQL statements.
 */
interface PostFetchInterface
{
    public static function fromContext(PostFetchContext $context): static;
}
