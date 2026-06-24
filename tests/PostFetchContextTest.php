<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use PHPUnit\Framework\TestCase;
use Ray\MediaQuery\Annotation\WebQuery;

class PostFetchContextTest extends TestCase
{
    public function testFromContext(): void
    {
        $entities = [
            new FakeProductEntity('Widget', 100),
            new FakeProductEntity('Gadget', 200),
        ];
        $webQuery = new WebQuery(id: 'foo_product', type: 'row_list');
        $ctx = new PostFetchContext($entities, ['status' => 'active'], $webQuery);

        $list = FakeProductList::fromContext($ctx);

        $this->assertInstanceOf(FakeProductList::class, $list);
        $this->assertCount(2, $list->items);
        $this->assertSame(2, $list->total);
        $this->assertSame('Widget', $list->items[0]->name);
    }

    public function testContextHoldsWebQueryAnnotation(): void
    {
        $webQuery = new WebQuery(id: 'test_id', type: 'row', factory: 'MyFactory');
        $ctx = new PostFetchContext([], ['key' => 'val'], $webQuery);
        $this->assertSame('test_id', $ctx->webQuery->id);
        $this->assertSame('row', $ctx->webQuery->type);
        $this->assertSame('MyFactory', $ctx->webQuery->factory);
    }
}
