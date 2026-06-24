<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Exception;

use function implode;

/**
 * Thrown when a required constructor parameter is missing from the JSON row,
 * or when the entity class does not exist.
 */
final class InvalidWebEntityException extends LogicException
{
    /**
     * @param string        $entity Entity class name.
     * @param string        $param  Missing parameter name (empty when class not found).
     * @param list<string>  $keys   Available JSON keys.
     */
    public function __construct(string $entity, string $param, array $keys)
    {
        $available = implode(', ', $keys);
        $message = $param !== ''
            ? "Entity '{$entity}' requires parameter '{$param}' but it was not found in JSON keys: [{$available}]"
            : "Entity class not found: {$entity}";
        parent::__construct($message);
    }
}
