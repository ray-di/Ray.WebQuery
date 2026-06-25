<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Closure;
use Override;
use Ray\Di\InjectorInterface;
use Ray\MediaQuery\Annotation\Qualifier\FactoryMethod;
use Ray\MediaQuery\Annotation\WebQuery;
use Ray\MediaQuery\Exception\EntityWithoutConstructorException;
use Ray\MediaQuery\Exception\InvalidWebEntityException;
use Ray\MediaQuery\Exception\InvalidWebFactoryException;
use Ray\MediaQuery\Exception\MissingResponseKeyException;
use ReflectionClass;
use ReflectionMethod;

use function array_is_list;
use function array_key_exists;
use function array_keys;
use function array_map;
use function class_exists;
use function method_exists;

/**
 * Maps a decoded JSON response into domain objects.
 *
 * A single converter — built from the #[WebQuery] factory or the return-type
 * entity — turns one response row (an associative array) into one object. The
 * mapper then applies it to a single object (`row`) or to a list (`row_list`).
 *
 * There is no PDO statement or fetch mode here: the response body is already an
 * array, so the conversion is a single bind-and-call. The key-to-parameter
 * binding lives in one place ({@see self::bind()}) for every converter.
 */
final class WebResponseMapper implements WebResponseMapperInterface
{
    public function __construct(
        private InjectorInterface $injector,
        #[FactoryMethod]
        private string $factoryMethod,
    ) {
    }

    /**
     * @param class-string|null $entity
     * @param array<mixed>      $body
     */
    #[Override]
    public function map(WebQuery $webQuery, string|null $entity, bool $isRow, array $body): mixed
    {
        $convert = $this->converter($webQuery, $entity);

        return $isRow ? $this->row($body, $convert) : $this->list($body, $convert);
    }

    /** @param array<mixed> $body */
    private function row(array $body, Closure $convert): mixed
    {
        if ($body === []) {
            return null;
        }

        /** @var array<string, mixed>|null $row */
        $row = array_is_list($body) ? ($body[0] ?? null) : $body;

        return $row === null ? null : $convert($row);
    }

    /**
     * @param array<mixed> $body
     *
     * @return array<mixed>
     */
    private function list(array $body, Closure $convert): array
    {
        if ($body === []) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $rows */
        $rows = array_is_list($body) ? $body : [$body];

        return array_map($convert, $rows);
    }

    /** @param class-string|null $entity */
    private function converter(WebQuery $webQuery, string|null $entity): Closure
    {
        if ($webQuery->factory !== '') {
            return $this->factoryConverter($webQuery->factory);
        }

        return $this->entityConverter($entity);
    }

    private function factoryConverter(string $factory): Closure
    {
        $method = $this->factoryMethod;
        if (! class_exists($factory) || ! method_exists($factory, $method)) {
            throw new InvalidWebFactoryException($factory, $method);
        }

        $ref = new ReflectionMethod($factory, $method);
        if (! $ref->isPublic()) {
            throw new InvalidWebFactoryException($factory, $method);
        }

        if ($ref->isStatic()) {
            return function (array $row) use ($factory, $method, $ref): mixed {
                /** @var array<string, mixed> $row */
                $args = $this->bind($ref, $row);

                /** @psalm-suppress MixedMethodCall */
                return $factory::$method(...$args);
            };
        }

        $instance = $this->injector->getInstance($factory);

        return function (array $row) use ($instance, $method, $ref): mixed {
            /** @var array<string, mixed> $row */
            $args = $this->bind($ref, $row);

            /** @psalm-suppress MixedMethodCall */
            return $instance->$method(...$args);
        };
    }

    /** @param class-string|null $entity */
    private function entityConverter(string|null $entity): Closure
    {
        if ($entity === null || ! class_exists($entity)) {
            throw new InvalidWebEntityException((string) $entity);
        }

        $ctor = (new ReflectionClass($entity))->getConstructor();
        if ($ctor === null) {
            throw new EntityWithoutConstructorException($entity);
        }

        return function (array $row) use ($entity, $ctor): object {
            /** @var array<string, mixed> $row */
            $args = $this->bind($ctor, $row);

            /** @psalm-suppress MixedMethodCall */
            return new $entity(...$args);
        };
    }

    /**
     * Match response keys to the target parameters by name.
     *
     * - Extra keys are discarded.
     * - Missing keys with a default value are omitted (the default is used).
     * - Missing nullable keys receive null.
     * - Missing required keys throw MissingResponseKeyException.
     *
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function bind(ReflectionMethod $ref, array $row): array
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

            $target = $ref->getDeclaringClass()->getName() . '::' . $ref->getName();

            throw new MissingResponseKeyException($target, $name, array_keys($row));
        }

        return $args;
    }
}
