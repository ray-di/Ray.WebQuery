<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Exception;

use function implode;

/**
 * Thrown when a required constructor or factory parameter is absent from the
 * decoded HTTP response.
 */
final class MissingResponseKeyException extends LogicException
{
    /**
     * @param string       $target        Target being built, e.g. "App\Product::__construct".
     * @param string       $missingParam  The parameter that was not found.
     * @param list<string> $availableKeys Keys present in the response.
     */
    public function __construct(string $target, string $missingParam, array $availableKeys)
    {
        $available = implode(', ', $availableKeys);
        parent::__construct(
            "'{$target}' requires parameter '{$missingParam}' but it was not found in response keys: [{$available}]",
        );
    }
}
