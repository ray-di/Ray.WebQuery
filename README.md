# Ray.WebQuery

Web API access mapping framework for [Ray.MediaQuery](https://github.com/ray-di/Ray.MediaQuery).

## Installation

```bash
composer require ray/media-query-web
```

Note: This package depends on `ray/media-query` which provides the core database query functionality.

## Usage

### Web API Query

Create an interface with `#[WebQuery]` attribute:

```php
<?php
use Ray\MediaQuery\Annotation\WebQuery;

interface ApiInterface
{
    #[WebQuery('api.get')]
    public function get(string $id): array;
}
```

Create a web query configuration file `web_query.json`:

```json
{
  "api": {
    "get": {
      "method": "GET",
      "path": "https://api.example.com/users/{id}"
    }
  }
}
```

Install the module:

```php
<?php
use Ray\MediaQuery\MediaQueryWebModule;
use Ray\MediaQuery\WebQueryConfig;
use Ray\Di\Injector;

$webConfig = new WebQueryConfig('web_query.json', ['domain' => 'example.com']);
$module = new MediaQueryWebModule($webConfig);
$injector = new Injector($module);

$api = $injector->getInstance(ApiInterface::class);
$result = $api->get('123'); // GET https://api.example.com/users/123
```

## Features

- **Web API Queries**: Execute HTTP requests via interface methods
- **URI Template Support**: Dynamic URL parameter binding
- **Multiple Response Types**: JSON array, string, or PSR-7 message
- **Parameter Injection**: Automatic parameter conversion and injection
- **HTTP Client Integration**: Built on Guzzle HTTP client

## Requirements

- PHP 8.1+
- ray/media-query ^1.0
