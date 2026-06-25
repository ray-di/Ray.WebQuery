<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Exception;

/**
 * Thrown when an entity class has no constructor.
 *
 * The Web layer hydrates entities through constructor named arguments, so a
 * class without a constructor cannot receive the response data.
 */
final class EntityWithoutConstructorException extends LogicException
{
    /** @param string $entity Entity class name. */
    public function __construct(string $entity)
    {
        parent::__construct("Entity '{$entity}' has no constructor; constructor hydration requires one");
    }
}
