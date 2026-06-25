# Ray.WebQuery

Web API access mapping framework for [Ray.MediaQuery](https://github.com/ray-di/Ray.MediaQuery).

## Installation

```bash
composer require ray/web-query
```

Note: This package builds on `ray/media-query`, which provides the core query infrastructure (parameter injection, logging, etc.).

## Usage

### Define an interface

Annotate the methods with `#[WebQuery]`, giving each one a query ID:

```php
<?php
use Ray\MediaQuery\Annotation\WebQuery;

interface UserApiInterface
{
    #[WebQuery('user_item')]
    public function get(string $id): array;
}
```

### Create a web query configuration file

`web_query.json` maps each query ID to an HTTP method and a URI template:

```json
{
  "webQuery": [
    {"id": "user_item", "method": "GET", "path": "https://{domain}/users/{id}"}
  ]
}
```

### Install the module

`MediaQueryWebModule` is installed into `MediaQueryBaseModule`. The interfaces are
registered with `Queries::fromClasses()`:

```php
<?php
use Ray\Di\Injector;
use Ray\MediaQuery\MediaQueryBaseModule;
use Ray\MediaQuery\MediaQueryWebModule;
use Ray\MediaQuery\Queries;
use Ray\MediaQuery\WebQueryConfig;

$queries = Queries::fromClasses([UserApiInterface::class]);
$webConfig = new WebQueryConfig('web_query.json', ['domain' => 'api.example.com']);

$module = new MediaQueryBaseModule($queries);
$module->install(new MediaQueryWebModule($webConfig));

$injector = new Injector($module);
$api = $injector->getInstance(UserApiInterface::class);

$user = $api->get('123'); // GET https://api.example.com/users/123
```

URI template variables are filled from the method arguments and the bindings
passed to `WebQueryConfig` (here `{domain}` comes from the bindings and `{id}`
from the `get()` argument).

### Response types

The return type of the interface method selects how the HTTP response is handled:

| Return type             | Result                              |
|-------------------------|-------------------------------------|
| `array`                 | JSON body decoded to an array       |
| `string`                | Raw response body                   |
| PSR-7 `MessageInterface`| The HTTP message object             |

### Mapping responses to a domain object

Instead of a raw array, a method can return typed, immutable domain objects.
Give `#[WebQuery]` a `factory` (and a `type`) — this is the same factory
mechanism `ray/media-query` provides for `#[DbQuery]`, applied to HTTP
responses.

```php
<?php
use Ray\MediaQuery\Annotation\WebQuery;

interface ProductApiInterface
{
    #[WebQuery('product_item', type: 'row', factory: ProductFactory::class)]
    public function get(string $id): Product;

    #[WebQuery('product_list', factory: ProductFactory::class)]
    /** @return array<Product> */
    public function list(string $status): array;
}
```

The factory is resolved through the DI injector, so it can depend on domain
services and apply business logic while building the object:

```php
<?php
final class ProductFactory
{
    public function __construct(
        private TaxCalculator $tax,
    ) {
    }

    public function factory(string $name, int $price): Product
    {
        return new Product($name, $this->tax->applyTax($price));
    }
}

final class Product
{
    public function __construct(
        public readonly string $name,
        public readonly int $price,
    ) {
    }
}
```

The decoded JSON is passed to the factory method as **named arguments**: each
JSON key is matched to a parameter by name, unknown keys are ignored, and a
missing required argument throws `InvalidWebFactoryKeyException`. (This is the
web counterpart of media-query's positional `PDO::FETCH_FUNC` binding.)

`type` selects single object vs. list:

| `type`       | JSON response            | Result          |
|--------------|--------------------------|-----------------|
| `'row'`      | object `{...}`           | one object      |
| `'row_list'` | array `[{...}, {...}]`   | `array<Object>` |

`type` defaults to `'row_list'`. A `'row'` method whose response is a list
takes the first element; a `'row_list'` method whose response is a single
object wraps it into a one-element list.

You can also map straight to an entity **without** a factory: when the return
type (or the `@return array<Entity>` docblock) is a class, each response is
hydrated through the entity constructor.

```php
#[WebQuery('product_item', type: 'row')]
public function get(string $id): Product; // built via Product::__construct
```

### Composing results with PostFetch

To wrap or aggregate the fetched objects into another type (totals, metadata,
…), let the return type implement `PostFetchInterface`. Its static
`fromContext()` receives the fetch result and returns the final object. It runs
after the factory, carries no dependencies by design, and is the web analogue
of media-query's `PostQueryInterface` (named *PostFetch* because a web call is a
single fetch, with no multi-statement query context to span).

```php
<?php
use Ray\MediaQuery\PostFetchContext;
use Ray\MediaQuery\PostFetchInterface;

final class ProductList implements PostFetchInterface
{
    /** @param array<Product> $items */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
    ) {
    }

    public static function fromContext(PostFetchContext $context): static
    {
        /** @var array<Product> $items */
        $items = is_array($context->result) ? $context->result : [];

        return new self($items, count($items));
    }
}
```

```php
#[WebQuery('product_list', factory: ProductFactory::class)]
public function listAggregate(string $status): ProductList;
```

`PostFetchContext` exposes the fetch `result`, the original method arguments
(`query`), and the `#[WebQuery]` annotation (`webQuery`).

## Features

- **Web API Queries**: Execute HTTP requests via interface methods
- **URI Template Support**: Dynamic URL parameter binding with `{param}` syntax
- **Multiple Response Types**: JSON array, string, or PSR-7 message
- **Domain Object Mapping (BDR)**: Map responses to typed domain objects via an injectable factory, with optional `PostFetch` composition
- **Parameter Injection**: Automatic parameter conversion and injection
- **HTTP Client Integration**: Built on the Guzzle HTTP client

## Requirements

- PHP 8.2+
- ray/media-query ^1.0
