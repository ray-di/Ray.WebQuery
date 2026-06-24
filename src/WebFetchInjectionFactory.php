<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Ray\Di\InjectorInterface;
use Ray\MediaQuery\Exception\InvalidWebFactoryKeyException;
use ReflectionMethod;

use function array_key_exists;
use function assert;
use function class_exists;
use function method_exists;

/**
 * Resolves a factory instance via the DI injector, then calls its factory
 * method with named arguments spread from the JSON row.
 *
 * This is the BDR (Behaviour-Driven Record) core:
 * the factory can have its own constructor-injected services (e.g. TaxCalculator).
 */
final class WebFetchInjectionFactory implements WebFetchInterface
{
    /** @param array{0: string, 1: string} $factory */
    public function __construct(
        private array $factory,
        private string $factoryMethod,
    ) {
    }

    /** @param array<string, mixed> $row */
    #[Override]
    public function fetchRow(array $row, InjectorInterface $injector): mixed
    {
        return $this->callFactory($row, $injector);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<mixed>
     */
    #[Override]
    public function fetchAll(array $rows, InjectorInterface $injector): array
    {
        return array_map(fn (array $row): mixed => $this->callFactory($row, $injector), $rows);
    }

    /** @param array<string, mixed> $row */
    private function callFactory(array $row, InjectorInterface $injector): mixed
    {
        /** @var class-string $factoryClass */
        $factoryClass = $this->factory[0];
        assert(class_exists($factoryClass));

        $factory = $injector->getInstance($factoryClass);
        $method = $this->factoryMethod;
        assert(method_exists($factory, $method));

        $ref = new ReflectionMethod($factory, $method);
        $args = $this->buildArgs($ref, $row);

        /** @psalm-suppress MixedMethodCall */
        return $factory->$method(...$args);
    }

    /**
     * Build named-argument array filtered by factory method parameter names.
     *
     * - Extra JSON keys are silently discarded.
     * - Missing keys with a default value are omitted (PHP uses the default).
     * - Missing keys that are nullable receive null explicitly.
     * - Missing required non-nullable keys throw InvalidWebFactoryKeyException.
     *
     * @param array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildArgs(ReflectionMethod $ref, array $row): array
    {
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
                $this->factory[0],
                $this->factoryMethod,
                $name,
                array_keys($row),
            );
        }

        return $args;
    }
}
