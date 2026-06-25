<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use PHPUnit\Framework\TestCase;
use Ray\MediaQuery\Annotation\WebQuery;
use Ray\MediaQuery\Exception\EntityWithoutConstructorException;
use Ray\MediaQuery\Exception\InvalidWebEntityException;
use Ray\MediaQuery\Exception\InvalidWebFactoryException;
use Ray\MediaQuery\Exception\MissingResponseKeyException;

class WebResponseMapperTest extends TestCase
{
    private function mapper(FakeInjector|null $injector = null): WebResponseMapper
    {
        return new WebResponseMapper($injector ?? new FakeInjector(), 'factory');
    }

    public function testEntityRow(): void
    {
        $row = ['name' => 'Widget', 'price' => 100];
        $result = $this->mapper()->map(new WebQuery('id'), FakeProductEntity::class, true, $row);
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        /** @var FakeProductEntity $result */
        $this->assertSame('Widget', $result->name);
        $this->assertSame(100, $result->price);
    }

    public function testEntityRowList(): void
    {
        $body = [['name' => 'Widget', 'price' => 100], ['name' => 'Gadget', 'price' => 200]];
        $result = $this->mapper()->map(new WebQuery('id'), FakeProductEntity::class, false, $body);
        /** @var array<FakeProductEntity> $result */
        $this->assertCount(2, $result);
        $this->assertInstanceOf(FakeProductEntity::class, $result[0]);
        $this->assertSame('Gadget', $result[1]->name);
    }

    public function testInjectedFactoryAppliesBusinessLogic(): void
    {
        $injector = (new FakeInjector())->bind(new FakeProductFactory(new FakeTaxCalculator()));
        $webQuery = new WebQuery(id: 'id', factory: FakeProductFactory::class);
        $result = $this->mapper($injector)->map($webQuery, null, true, ['name' => 'Widget', 'price' => 100]);
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        // FakeTaxCalculator applies *1.1: 100 -> 110
        /** @var FakeProductEntity $result */
        $this->assertSame(110, $result->price);
    }

    public function testStaticFactory(): void
    {
        $webQuery = new WebQuery(id: 'id', factory: FakeStaticProductFactory::class);
        $result = $this->mapper()->map($webQuery, null, true, ['name' => 'Widget', 'price' => 100]);
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        /** @var FakeProductEntity $result */
        $this->assertSame(100, $result->price);
    }

    public function testExtraKeysAreIgnored(): void
    {
        $row = ['name' => 'Widget', 'price' => 100, 'extra' => 'x'];
        $result = $this->mapper()->map(new WebQuery('id'), FakeProductEntity::class, true, $row);
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        /** @var FakeProductEntity $result */
        $this->assertSame('Widget', $result->name);
    }

    public function testMissingRequiredKeyThrows(): void
    {
        $this->expectException(MissingResponseKeyException::class);
        $this->mapper()->map(new WebQuery('id'), FakeProductEntity::class, true, ['name' => 'Widget']);
    }

    public function testEntityWithoutConstructorThrows(): void
    {
        $noCtor = new class {
            public string $name = '';
        };
        $this->expectException(EntityWithoutConstructorException::class);
        $this->mapper()->map(new WebQuery('id'), $noCtor::class, true, ['name' => 'x']);
    }

    public function testInvalidFactoryThrows(): void
    {
        /** @var class-string $missing */
        $missing = 'NotAClass\\Factory';
        $webQuery = new WebQuery(id: 'id', factory: $missing);
        $this->expectException(InvalidWebFactoryException::class);
        $this->mapper()->map($webQuery, null, true, ['name' => 'Widget']);
    }

    public function testEntityClassNotFoundThrows(): void
    {
        /** @var class-string $missing */
        $missing = 'NotAnEntity\\Klass';
        $this->expectException(InvalidWebEntityException::class);
        $this->mapper()->map(new WebQuery('id'), $missing, true, ['name' => 'x']);
    }

    public function testEmptyBodyRowReturnsNull(): void
    {
        $this->assertNull($this->mapper()->map(new WebQuery('id'), FakeProductEntity::class, true, []));
    }

    public function testEmptyBodyRowListReturnsEmptyArray(): void
    {
        $this->assertSame([], $this->mapper()->map(new WebQuery('id'), FakeProductEntity::class, false, []));
    }

    public function testRowFromListTakesFirstElement(): void
    {
        $body = [['name' => 'Widget', 'price' => 100], ['name' => 'Gadget', 'price' => 200]];
        $result = $this->mapper()->map(new WebQuery('id'), FakeProductEntity::class, true, $body);
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        /** @var FakeProductEntity $result */
        $this->assertSame('Widget', $result->name);
    }

    public function testRowListWrapsSingleObject(): void
    {
        $row = ['name' => 'Widget', 'price' => 100];
        $result = $this->mapper()->map(new WebQuery('id'), FakeProductEntity::class, false, $row);
        /** @var array<FakeProductEntity> $result */
        $this->assertCount(1, $result);
        $this->assertSame('Widget', $result[0]->name);
    }
}
