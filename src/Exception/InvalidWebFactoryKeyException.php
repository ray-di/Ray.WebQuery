<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Exception;

use function implode;

/**
 * Thrown when a required factory method parameter is missing from the JSON row.
 */
final class InvalidWebFactoryKeyException extends LogicException
{
    /**
     * @param string       $factoryClass  Factory class name.
     * @param string       $method        Factory method name.
     * @param string       $missingParam  The parameter that was not found.
     * @param list<string> $availableKeys Available JSON keys.
     */
    public function __construct(
        string $factoryClass,
        string $method,
        string $missingParam,
        array $availableKeys,
    ) {
        $available = implode(', ', $availableKeys);
        $message = "Factory '{$factoryClass}::{$method}()' requires parameter"
            . " '{$missingParam}' but it was not found in JSON keys: [{$available}]";
        parent::__construct($message);
    }
}
