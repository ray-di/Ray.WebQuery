<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;
use RuntimeException;

/**
 * Minimal InjectorInterface fake for unit tests.
 *
 * Bind concrete instances with bind() and retrieve them by class name via
 * getInstance().
 */
final class FakeInjector implements InjectorInterface
{
    /** @var array<class-string, object> */
    private array $instances = [];

    public function bind(object $instance): self
    {
        $this->instances[$instance::class] = $instance;

        return $this;
    }

    /**
     * @param ''|class-string $interface
     * @param string          $name
     */
    #[Override]
    public function getInstance($interface, $name = Name::ANY): mixed
    {
        if (isset($this->instances[$interface])) {
            return $this->instances[$interface];
        }

        throw new RuntimeException("FakeInjector: not bound: {$interface}");
    }
}
