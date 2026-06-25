<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\MediaQuery\WebApi\FooProductInterface;

class WebQueryBdrTest extends TestCase
{
    private FooProductInterface $fooProduct;

    /** @param non-empty-string $body JSON body for the fake HTTP client */
    private function buildFooProduct(string $body): FooProductInterface
    {
        $mediaQueries = Queries::fromClasses([FooProductInterface::class]);
        $mediaQueryJson = __DIR__ . '/Fake/web_product.json';
        $webModule = new MediaQueryWebModule(new WebQueryConfig($mediaQueryJson, ['domain' => 'api.example.com']));
        $baseModule = new MediaQueryBaseModule($mediaQueries);
        $baseModule->install($webModule);
        $baseModule->override(new FakeProductModule($body));

        return (new Injector($baseModule))->getInstance(FooProductInterface::class);
    }

    public function testEntityWithConstructor(): void
    {
        $body = '{"name":"Widget","price":100}';
        $this->fooProduct = $this->buildFooProduct($body);
        $result = $this->fooProduct->get('1');
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        $this->assertSame('Widget', $result->name);
        $this->assertSame(100, $result->price);
    }

    public function testInjectedFactoryAppliesTax(): void
    {
        $body = '{"name":"Widget","price":100}';
        $this->fooProduct = $this->buildFooProduct($body);
        $result = $this->fooProduct->getWithTax('1');
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        // FakeTaxCalculator * 1.1 = 110
        $this->assertSame(110, $result->price);
    }

    public function testStaticFactoryBuildsEntity(): void
    {
        $body = '{"name":"Widget","price":100}';
        $this->fooProduct = $this->buildFooProduct($body);
        $result = $this->fooProduct->getStatic('1');
        $this->assertInstanceOf(FakeProductEntity::class, $result);
        $this->assertSame('Widget', $result->name);
        $this->assertSame(100, $result->price);
    }

    public function testRowListWithFactory(): void
    {
        $body = '[{"name":"Widget","price":100},{"name":"Gadget","price":200}]';
        $this->fooProduct = $this->buildFooProduct($body);
        $result = $this->fooProduct->listWithTax('active');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(FakeProductEntity::class, $result[0]);
        $this->assertSame(110, $result[0]->price);
        $this->assertSame(220, $result[1]->price);
    }

    public function testRowListWithoutFactory(): void
    {
        $body = '[{"name":"Widget","price":100},{"name":"Gadget","price":200}]';
        $this->fooProduct = $this->buildFooProduct($body);
        $result = $this->fooProduct->list('active');
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(FakeProductEntity::class, $result[0]);
    }

    public function testPostFetchWrapsResultInAggregate(): void
    {
        $body = '[{"name":"Widget","price":100},{"name":"Gadget","price":200}]';
        $this->fooProduct = $this->buildFooProduct($body);
        $result = $this->fooProduct->getList('active');
        $this->assertInstanceOf(FakeProductList::class, $result);
        $this->assertSame(2, $result->total);
        $this->assertCount(2, $result->items);
    }

    public function testLegacyArrayPathUnchanged(): void
    {
        // FooItemInterface::item() still hits legacy path (array return, no factory, no entity)
        $mediaQueries = Queries::fromClasses([WebApi\FooItemInterface::class]);
        $mediaQueryJson = __DIR__ . '/Fake/web_query.json';
        $webModule = new MediaQueryWebModule(new WebQueryConfig($mediaQueryJson, ['domain' => 'ray-di.github.io']));
        $baseModule = new MediaQueryBaseModule($mediaQueries);
        $baseModule->install($webModule);
        $baseModule->override(new FakeWebClientModule());
        $injector = new Injector($baseModule);
        $fooItem = $injector->getInstance(WebApi\FooItemInterface::class);
        $result = $fooItem->item('web_query');
        $this->assertSame('Web query schema', $result['title']);
    }
}
