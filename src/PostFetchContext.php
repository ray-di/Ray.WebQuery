<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Ray\MediaQuery\Annotation\WebQuery;

/**
 * Value object passed to PostFetchInterface::fromContext().
 *
 * Carries the fetch result, the original method arguments, and the
 * #[WebQuery] annotation so that post-processors have full context.
 */
final class PostFetchContext
{
    /**
     * @param mixed                 $result   Output of fetchRow() or fetchAll().
     * @param array<string, string> $query    Method arguments passed to the interceptor.
     * @param WebQuery              $webQuery The annotation (id, type, factory).
     */
    public function __construct(
        public readonly mixed $result,
        public readonly array $query,
        public readonly WebQuery $webQuery,
    ) {
    }
}
