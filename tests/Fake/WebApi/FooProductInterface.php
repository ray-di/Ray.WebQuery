<?php

declare(strict_types=1);

namespace Ray\MediaQuery\WebApi;

use Ray\MediaQuery\Annotation\WebQuery;
use Ray\MediaQuery\FakeProductEntity;
use Ray\MediaQuery\FakeProductFactory;
use Ray\MediaQuery\FakeProductList;
use Ray\MediaQuery\FakeStaticProductFactory;

interface FooProductInterface
{
    /** Returns an entity built via its constructor. */
    #[WebQuery(id: 'foo_product', type: 'row')]
    public function get(string $id): FakeProductEntity;

    /** Returns a list of entities built via their constructor. */
    /** @return array<FakeProductEntity> */
    #[WebQuery(id: 'foo_product', type: 'row_list')]
    public function list(string $status): array;

    /** Returns an entity via a DI-resolved instance factory. */
    #[WebQuery(id: 'foo_product', type: 'row', factory: FakeProductFactory::class)]
    public function getWithTax(string $id): FakeProductEntity;

    /** Returns a list via a DI-resolved instance factory. */
    /** @return array<FakeProductEntity> */
    #[WebQuery(id: 'foo_product', type: 'row_list', factory: FakeProductFactory::class)]
    public function listWithTax(string $status): array;

    /** Returns an entity via a static factory. */
    #[WebQuery(id: 'foo_product', type: 'row', factory: FakeStaticProductFactory::class)]
    public function getStatic(string $id): FakeProductEntity;

    /** A genuine union return type maps to a single object even without type: 'row'. */
    #[WebQuery(id: 'foo_product', factory: FakeProductFactory::class)]
    public function getUnion(string $id): FakeProductEntity|FakeProductList;

    /** Returns a PostFetch aggregate object. */
    #[WebQuery(id: 'foo_product', type: 'row_list', factory: FakeProductFactory::class)]
    public function getList(string $status): FakeProductList;
}
