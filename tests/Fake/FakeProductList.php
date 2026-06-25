<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;

use function is_array;

final class FakeProductList implements PostFetchInterface
{
    /** @param array<FakeProductEntity> $items */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
    ) {
    }

    #[Override]
    public static function fromContext(PostFetchContext $context): static
    {
        /** @var array<FakeProductEntity> $items */
        $items = is_array($context->result) ? $context->result : [];

        return new self($items, count($items));
    }
}
