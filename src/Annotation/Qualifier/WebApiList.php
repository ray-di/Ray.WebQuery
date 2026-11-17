<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Annotation\Qualifier;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Qualifies the method and path of each web API, keyed by its id
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class WebApiList
{
}
