<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use PHPUnit\Framework\TestCase;
use Ray\MediaQuery\Exception\EntityWithoutConstructorException;
use Ray\MediaQuery\Exception\InvalidWebEntityException;

class WebFetchNewInstanceTest extends TestCase
{
    private FakeInjector $injector;

    protected function setUp(): void
    {
        $this->injector = new FakeInjector();
    }

    public function testFetchRow(): void
    {
        $fetch = new WebFetchNewInstance(FakeProductEntity::class);
        $result = $fetch->fetchRow(['name' => 'Widget', 'price' => 100], $this->injector);
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        $this->assertSame('Widget', $result->name);
        $this->assertSame(100, $result->price);
    }

    public function testFetchAll(): void
    {
        $fetch = new WebFetchNewInstance(FakeProductEntity::class);
        $rows = [
            ['name' => 'Widget', 'price' => 100],
            ['name' => 'Gadget', 'price' => 200],
        ];
        $result = $fetch->fetchAll($rows, $this->injector);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(FakeProductEntity::class, $result[0]);
        $this->assertSame('Gadget', $result[1]->name);
    }

    public function testExtraJsonKeysAreIgnored(): void
    {
        $fetch = new WebFetchNewInstance(FakeProductEntity::class);
        $row = ['name' => 'Widget', 'price' => 100, 'extra' => 'ignored'];
        $result = $fetch->fetchRow($row, $this->injector);
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        $this->assertSame('Widget', $result->name);
    }

    public function testMissingRequiredKeyThrows(): void
    {
        $fetch = new WebFetchNewInstance(FakeProductEntity::class);
        $this->expectException(InvalidWebEntityException::class);
        $fetch->fetchRow(['name' => 'Widget'], $this->injector);
    }

    public function testThrowsWhenEntityHasNoConstructor(): void
    {
        $noCtor = new class {
            public string $name = '';
        };
        $fetch = new WebFetchNewInstance($noCtor::class);
        $this->expectException(EntityWithoutConstructorException::class);
        $fetch->fetchRow(['name' => 'Widget'], $this->injector);
    }
}
