<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Ray\MediaQuery\Annotation\WebQuery;
use ReflectionNamedType;
use ReflectionUnionType;

interface WebFetchFactoryInterface
{
    /** @param class-string|null $entity */
    public function factory(
        WebQuery $webQuery,
        string|null $entity,
        ReflectionNamedType|ReflectionUnionType|null $returnType,
    ): WebFetchInterface;
}
