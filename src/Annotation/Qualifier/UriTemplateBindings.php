<?php

declare(strict_types=1);

namespace Ray\MediaQuery\Annotation\Qualifier;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Qualifies the values expanded into the URI templates of every web API
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class UriTemplateBindings
{
}
