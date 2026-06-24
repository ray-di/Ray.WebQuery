<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use PHPUnit\Framework\TestCase;

class WebFetchClassTest extends TestCase
{
    public function testFetchRowSetsPublicProperties(): void
    {
        $fetch = new WebFetchClass(FakeNoCtorEntity::class);
        $injector = new FakeInjector();
        $result = $fetch->fetchRow(['name' => 'Widget', 'price' => 100], $injector);
        $this->assertInstanceOf(FakeNoCtorEntity::class, $result);
        $this->assertSame('Widget', $result->name);
        $this->assertSame(100, $result->price);
    }

    public function testFetchAllReturnsMultipleObjects(): void
    {
        $fetch = new WebFetchClass(FakeNoCtorEntity::class);
        $injector = new FakeInjector();
        $rows = [
            ['name' => 'Widget', 'price' => 100],
            ['name' => 'Gadget', 'price' => 200],
        ];
        /** @var array<FakeNoCtorEntity> $result */
        $result = $fetch->fetchAll($rows, $injector);
        $this->assertCount(2, $result);
        $this->assertSame('Gadget', $result[1]->name);
    }
}
