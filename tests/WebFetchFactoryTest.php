<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use PHPUnit\Framework\TestCase;
use Ray\MediaQuery\Annotation\WebQuery;
use Ray\MediaQuery\Exception\InvalidWebEntityException;

class WebFetchFactoryTest extends TestCase
{
    private WebFetchFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new WebFetchFactory('factory');
    }

    public function testReturnsWebFetchAssocWhenNoFactoryNoEntity(): void
    {
        $webQuery = new WebQuery('id');
        $fetch = $this->factory->factory($webQuery, null, null);
        $this->assertInstanceOf(WebFetchAssoc::class, $fetch);
    }

    public function testReturnsWebFetchNewInstanceForEntityWithConstructor(): void
    {
        $webQuery = new WebQuery('id');
        $fetch = $this->factory->factory($webQuery, FakeProductEntity::class, null);
        $this->assertInstanceOf(WebFetchNewInstance::class, $fetch);
    }

    public function testReturnsWebFetchClassForEntityWithoutConstructor(): void
    {
        $webQuery = new WebQuery('id');
        $fetch = $this->factory->factory($webQuery, FakeNoCtorEntity::class, null);
        $this->assertInstanceOf(WebFetchClass::class, $fetch);
    }

    public function testReturnsWebFetchInjectionFactoryForInstanceFactory(): void
    {
        $webQuery = new WebQuery(id: 'id', factory: FakeProductFactory::class);
        $fetch = $this->factory->factory($webQuery, null, null);
        $this->assertInstanceOf(WebFetchInjectionFactory::class, $fetch);
    }

    public function testReturnsWebFetchStaticFactoryForStaticFactory(): void
    {
        $webQuery = new WebQuery(id: 'id', factory: FakeStaticProductFactory::class);
        $fetch = $this->factory->factory($webQuery, null, null);
        $this->assertInstanceOf(WebFetchStaticFactory::class, $fetch);
    }

    public function testThrowsForNonExistentEntityClass(): void
    {
        $webQuery = new WebQuery('id');
        $this->expectException(InvalidWebEntityException::class);
        /** @var class-string $nonExistent */
        $nonExistent = 'NonExistent\\Entity';
        $this->factory->factory($webQuery, $nonExistent, null);
    }
}
