<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

/**
 * Supplies a class name that does not exist, typed as class-string, to exercise
 * the mapper's guard against entities that cannot be loaded.
 */
final class FakeMissingEntity
{
    /** @return class-string */
    public static function name(): string
    {
        return 'NotAnEntity\\Klass';
    }
}
