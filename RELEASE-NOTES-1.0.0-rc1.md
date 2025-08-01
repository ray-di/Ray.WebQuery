# Release Notes - Ray.WebQuery 1.0.0-rc1

## 🚀 Initial Release

Ray.WebQuery 1.0.0-rc1 is the first release candidate of our new web API access mapping framework, extracted from Ray.MediaQuery to provide dedicated HTTP client functionality.

## ✨ Key Features

### Web API Query System
- **`#[WebQuery]` Annotation**: Mark interface methods for automatic HTTP request mapping
- **Multiple Response Types**: Support for JSON arrays, raw strings, and PSR-7 MessageInterface
- **URI Template Support**: Dynamic URL parameter binding using `{param}` syntax
- **AOP Integration**: Seamless method interception using Ray.Aop

### Configuration Management
- **JSON Configuration**: Define web APIs in structured JSON files
- **Schema Validation**: Complete JSON schema for web query definitions
- **Template Bindings**: Support for global URI template parameter bindings

### HTTP Client Integration
- **Guzzle HTTP Client**: Built on the reliable Guzzle HTTP library
- **PSR-7 Compatibility**: Full support for PSR-7 HTTP message interfaces
- **Parameter Injection**: Automatic parameter conversion and injection

## 🏗️ Architecture

The framework follows a clean separation of concerns:

- **MediaQueryWebModule**: Dependency injection configuration
- **WebQueryInterceptor**: AOP-based method interception
- **WebQueryConfig**: Configuration loading and validation
- **WebApiQuery**: HTTP request execution

## 📋 Requirements

- PHP 8.1+
- Ray.MediaQuery 1.0.0-rc1
- Guzzle HTTP 7.2+

## 🔧 Installation

```bash
composer require ray/web-query
```

## 📝 Basic Usage

```php
<?php
use Ray\MediaQuery\Annotation\WebQuery;

interface ApiInterface
{
    #[WebQuery('user.get')]
    public function getUser(string $id): array;
}
```

```json
{
  "webQuery": [
    {
      "id": "user.get",
      "method": "GET", 
      "path": "https://api.example.com/users/{id}"
    }
  ]
}
```

## 🧪 Testing

This release includes comprehensive test coverage for all major functionality, ensuring reliability and stability for production use.

## 🔗 Dependencies

This package builds upon:
- ray/media-query 1.0.0-rc1 for core query functionality
- ray/di and ray/aop for dependency injection and AOP
- guzzlehttp/guzzle for HTTP client capabilities
- rize/uri-template for URI template processing

## 🎯 Next Steps

As a release candidate, we welcome feedback and testing from the community. The API is considered stable, but we may make minor adjustments based on user feedback before the final 1.0.0 release.