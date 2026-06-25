<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Exception;

/**
 * Thrown when #[WebQuery(factory: ...)] points to a class or method that is
 * neither a static method nor a class resolvable via DI.
 */
final class InvalidWebFactoryException extends LogicException
{
    public function __construct(string $factory, string $method)
    {
        parent::__construct(
            "Factory '{$factory}::{$method}()' is not callable; provide a static method or a class resolvable via DI",
        );
    }
}
