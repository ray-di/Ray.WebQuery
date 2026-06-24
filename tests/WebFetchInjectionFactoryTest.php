<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use PHPUnit\Framework\TestCase;

class WebFetchInjectionFactoryTest extends TestCase
{
    public function testFetchRowAppliesBusinessLogic(): void
    {
        $taxCalc = new FakeTaxCalculator();
        $productFactory = new FakeProductFactory($taxCalc);
        $injector = (new FakeInjector())->bind($productFactory);

        $fetch = new WebFetchInjectionFactory([FakeProductFactory::class, 'factory'], 'factory');
        $result = $fetch->fetchRow(['name' => 'Widget', 'price' => 100], $injector);

        $this->assertInstanceOf(FakeProductEntity::class, $result);
        // FakeTaxCalculator applies *1.1: 100 -> 110
        /** @var FakeProductEntity $result */
        $this->assertSame(110, $result->price);
    }

    public function testFetchAllAppliesBusinessLogicToAllRows(): void
    {
        $productFactory = new FakeProductFactory(new FakeTaxCalculator());
        $injector = (new FakeInjector())->bind($productFactory);

        $fetch = new WebFetchInjectionFactory([FakeProductFactory::class, 'factory'], 'factory');
        $rows = [
            ['name' => 'Widget', 'price' => 100],
            ['name' => 'Gadget', 'price' => 200],
        ];
        /** @var array<FakeProductEntity> $result */
        $result = $fetch->fetchAll($rows, $injector);

        $this->assertCount(2, $result);
        $this->assertSame(110, $result[0]->price);
        $this->assertSame(220, $result[1]->price);
    }

    public function testExtraJsonKeysAreIgnored(): void
    {
        $productFactory = new FakeProductFactory(new FakeTaxCalculator());
        $injector = (new FakeInjector())->bind($productFactory);

        $fetch = new WebFetchInjectionFactory([FakeProductFactory::class, 'factory'], 'factory');
        $row = ['name' => 'Widget', 'price' => 100, 'extra' => 'ignored'];
        $result = $fetch->fetchRow($row, $injector);
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        /** @var FakeProductEntity $result */
        $this->assertSame('Widget', $result->name);
    }
}
