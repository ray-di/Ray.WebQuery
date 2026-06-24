<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Ray\Di\InjectorInterface;
use Ray\MediaQuery\Exception\InvalidWebEntityException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

use function array_key_exists;

/**
 * Builds an entity by spreading named arguments into the constructor.
 *
 * Used when the entity class has a constructor
 * (analogous to PDO::FETCH_FUNC with new $entity(...$args)).
 */
final class WebFetchNewInstance implements WebFetchInterface
{
    /** @param class-string $entity */
    public function __construct(private string $entity)
    {
    }

    /** @param array<string, mixed> $row */
    #[Override]
    public function fetchRow(array $row, InjectorInterface $injector): object
    {
        $entity = $this->entity;
        $ref = new ReflectionClass($entity);
        $ctor = $ref->getConstructor();
        $args = $ctor !== null ? $this->buildArgs($ctor, $row) : [];

        /** @psalm-suppress MixedMethodCall */
        return new $entity(...$args);
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

    /**
     * Build a named-argument array filtered by the constructor parameter names.
     *
     * - Extra JSON keys are silently discarded.
     * - Missing keys with a default value are omitted (PHP uses the default).
     * - Missing keys that are nullable receive null explicitly.
     * - Missing required non-nullable keys throw InvalidWebEntityException.
     *
     * @param array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildArgs(ReflectionMethod $ctor, array $row): array
    {
        $args = [];
        foreach ($ctor->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $row)) {
                /** @psalm-suppress MixedAssignment */
                $args[$name] = $row[$name];
                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                continue;
            }

            if ($param->allowsNull()) {
                $args[$name] = null;
                continue;
            }

            throw new InvalidWebEntityException($this->entity, $name, array_keys($row));
        }

        return $args;
    }
}
