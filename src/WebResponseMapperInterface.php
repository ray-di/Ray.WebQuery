<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Ray\MediaQuery\Annotation\WebQuery;

interface WebResponseMapperInterface
{
    /**
     * Map a decoded JSON response body into domain object(s).
     *
     * @param class-string|null $entity
     * @param array<mixed>      $body
     */
    public function map(WebQuery $webQuery, string|null $entity, bool $isRow, array $body): mixed;
}
