<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Ray\Di\InjectorInterface;

/**
 * Returns the raw associative array as-is.
 *
 * Also used as a sentinel value: when WebQueryInterceptor receives an instance
 * of this class it falls through to the legacy (array/string/Message) path.
 */
final class WebFetchAssoc implements WebFetchInterface
{
    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    #[Override]
    public function fetchRow(array $row, InjectorInterface $injector): array
    {
        return $row;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<mixed>
     */
    #[Override]
    public function fetchAll(array $rows, InjectorInterface $injector): array
    {
        return $rows;
    }
}
