<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Ray\Di\InjectorInterface;

use function property_exists;

/**
 * Builds an entity by setting public properties directly.
 *
 * Used when the entity class has no constructor (analogous to PDO::FETCH_CLASS).
 */
final class WebFetchClass implements WebFetchInterface
{
    /** @param class-string $entity */
    public function __construct(private string $entity)
    {
    }

    /** @param array<string, mixed> $row */
    #[Override]
    public function fetchRow(array $row, InjectorInterface $injector): object
    {
        /** @var class-string $entity */
        $entity = $this->entity;
        /** @psalm-suppress MixedMethodCall */
        $obj = new $entity();
        /**
         * @var string $key
         * @psalm-suppress MixedAssignment
         */
        foreach ($row as $key => $value) {
            if (property_exists($obj, $key)) {
                $obj->$key = $value;
            }
        }

        return $obj;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<mixed>
     */
    #[Override]
    public function fetchAll(array $rows, InjectorInterface $injector): array
    {
        return array_map(fn (array $row): object => $this->fetchRow($row, $injector), $rows);
    }
}
