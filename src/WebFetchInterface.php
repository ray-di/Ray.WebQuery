<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Ray\Di\InjectorInterface;

interface WebFetchInterface
{
    /**
     * Build a single object/array from one associative row.
     *
     * @param array<string, mixed> $row
     */
    public function fetchRow(array $row, InjectorInterface $injector): mixed;

    /**
     * Build an array of objects from a list of associative rows.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<mixed>
     */
    public function fetchAll(array $rows, InjectorInterface $injector): array;
}
