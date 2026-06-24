# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-06-25

First stable release. Requires PHP 8.2+ and stable `ray/media-query` (`^1.0`).

### Changed
- Relax the `rize/uri-template` constraint to `^0.3 || ^0.4` so the package can be installed alongside packages that require `rize/uri-template ^0.4` such as `bear/resource` 1.29+ and `ray/aura-sql-module` 1.17.x (#2)

### Fixed
- Make the web query test suite hermetic so it no longer depends on a live external schema URL (#3)
- Correct the package name (`ray/web-query`) and the usage examples in the README

## [1.0.0-rc1] - 2025-07-30

### Added
- Initial release of ray/media-query-web package
- Web API query functionality extracted from ray/media-query
- `WebQueryInterceptor` for intercepting `#[WebQuery]` annotated methods
- `WebApiQuery` for executing HTTP requests with URI template support
- `WebQueryConfig` for web query configuration management
- `MediaQueryWebModule` for dependency injection setup
- `WebQuery` annotation for marking web query methods
- `WebApiList` qualifier for web API configuration binding
- Support for multiple response types:
  - JSON array response (default)
  - Raw string response body
  - PSR-7 MessageInterface response
- URI template parameter binding with `{param}` syntax
- Integration with Guzzle HTTP client
- Parameter conversion and injection support via ray/media-query
- Comprehensive test coverage


[1.0.0]: https://github.com/ray-di/Ray.WebQuery/releases/tag/1.0.0
[1.0.0-rc1]: https://github.com/ray-di/Ray.MediaQuery-Web/releases/tag/1.0.0-rc1