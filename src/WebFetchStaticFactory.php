<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Ray\Di\InjectorInterface;
use Ray\MediaQuery\Exception\InvalidWebFactoryKeyException;
use ReflectionMethod;

use function array_key_exists;
use function array_keys;

/**
 * Calls a static factory method with named arguments spread from JSON row.
 *
 * Used when the factory method is a static callable
 * (analogous to PDO::FETCH_FUNC with a static callable).
 */
final class WebFetchStaticFactory implements WebFetchInterface
{
    /** @var callable */
    private $staticFactory;

    public function __construct(callable $staticFactory)
    {
        $this->staticFactory = $staticFactory;
    }

    /** @param array<string, mixed> $row */
    #[Override]
    public function fetchRow(array $row, InjectorInterface $injector): mixed
    {
        $callable = $this->staticFactory;
        $args = $this->buildArgs($row);

        return $callable(...$args);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<mixed>
     */
    #[Override]
    public function fetchAll(array $rows, InjectorInterface $injector): array
    {
        return array_map(fn (array $row): mixed => $this->fetchRow($row, $injector), $rows);
    }

    /**
     * Build named-argument array from the static method's parameter list.
     *
     * @param array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildArgs(array $row): array
    {
        /** @var array{0: class-string, 1: string} $callable */
        $callable = $this->staticFactory;
        $ref = new ReflectionMethod($callable[0], $callable[1]);
        $args = [];
        foreach ($ref->getParameters() as $param) {
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

            throw new InvalidWebFactoryKeyException(
                $callable[0],
                $callable[1],
                $name,
                array_keys($row),
            );
        }

        return $args;
    }
}
