<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Annotation;

use Attribute;

/**
 * Calls the web API registered under `id` with the method arguments and maps the response to the return type
 *
 * @see \Ray\MediaQuery\WebQueryInterceptor
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class WebQuery
{
    /**
     * @param string $id      `id` of an entry in the `webQuery` list of the media query JSON
     * @param string $type    `row` maps the response to one object, `row_list` to a list
     * @param string $factory Class whose `factory()` method builds each row, resolved through the injector
     */
    public function __construct(
        public string $id,
        public string $type = 'row_list',
        public string $factory = '',
    ) {
    }
}
