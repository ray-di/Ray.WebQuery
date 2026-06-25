<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Ray\MediaQuery\Annotation\Qualifier\FactoryMethod;
use Ray\MediaQuery\Annotation\WebQuery;
use Ray\MediaQuery\Exception\InvalidWebEntityException;
use ReflectionNamedType;
use ReflectionUnionType;

use function class_exists;
use function is_callable;
use function method_exists;

/**
 * Selects the appropriate WebFetchInterface implementation based on
 * the #[WebQuery] annotation and the resolved entity class.
 *
 * The selection logic mirrors FetchFactory from ray/media-query:
 *   1. static callable  → WebFetchStaticFactory
 *   2. instance factory → WebFetchInjectionFactory
 *   3. no entity        → WebFetchAssoc (sentinel for legacy path)
 *   4. entity missing   → InvalidWebEntityException
 *   5. entity           → WebFetchNewInstance
 */
final class WebFetchFactory implements WebFetchFactoryInterface
{
    public function __construct(
        #[FactoryMethod]
        private string $factoryMethod,
    ) {
    }

    /** @param class-string|null $entity */
    #[Override]
    public function factory(
        WebQuery $webQuery,
        string|null $entity,
        ReflectionNamedType|ReflectionUnionType|null $returnType,
    ): WebFetchInterface {
        unset($returnType);

        /** @var array{0: string, 1: string} $maybeFactory */
        $maybeFactory = [$webQuery->factory, $this->factoryMethod];

        if (is_callable($maybeFactory)) {
            return new WebFetchStaticFactory($maybeFactory);
        }

        if (class_exists($webQuery->factory) && method_exists($webQuery->factory, $this->factoryMethod)) {
            return new WebFetchInjectionFactory($maybeFactory, $this->factoryMethod);
        }

        if ($entity === null) {
            return new WebFetchAssoc();
        }

        if (! class_exists($entity)) {
            throw new InvalidWebEntityException($entity, '', []);
        }

        return new WebFetchNewInstance($entity);
    }
}
