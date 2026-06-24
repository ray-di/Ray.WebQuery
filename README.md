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

## Features

- **Web API Queries**: Execute HTTP requests via interface methods
- **URI Template Support**: Dynamic URL parameter binding with `{param}` syntax
- **Multiple Response Types**: JSON array, string, or PSR-7 message
- **Parameter Injection**: Automatic parameter conversion and injection
- **HTTP Client Integration**: Built on the Guzzle HTTP client

## Requirements

- PHP 8.1+
- ray/media-query ^1.0
