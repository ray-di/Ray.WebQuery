<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Exception;

/**
 * Thrown when the return-type entity class cannot be found.
 */
final class InvalidWebEntityException extends LogicException
{
    public function __construct(string $entity)
    {
        parent::__construct("Entity class not found: {$entity}");
    }
}
