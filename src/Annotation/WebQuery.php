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
     * @param string $type    `row` maps the response to one object; `row_list` maps it to a list for an `array`
     *                        or PostFetchInterface return type and to one object for another class or a union.
     *                        Where one object is mapped, an empty response gives null.
     *                        Read only with a factory or an entity.
     * @param string $factory Class whose `factory()` builds each row; a static `factory()` is called directly,
     *                        a non-static one on an instance from the injector
     */
    public function __construct(
        public string $id,
        public string $type = 'row_list',
        public string $factory = '',
    ) {
    }
}
