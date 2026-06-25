# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-06-25

### Added
- Map web API responses to typed domain objects (BDR read pattern). `#[WebQuery]` gains `type` (`'row'` | `'row_list'`) and `factory` parameters, mirroring `#[DbQuery]` (#5)
- `WebResponseMapper` hydrates a decoded JSON response into objects via a DI-resolved factory (static or instance) or the return-type entity constructor, binding response keys to parameter names (#5)
- `PostFetchInterface` / `PostFetchContext` to compose the mapped result into an aggregate return type through a static `fromContext()` named constructor (#5)
- Dedicated exceptions `InvalidWebEntityException`, `InvalidWebFactoryException`, `EntityWithoutConstructorException`, and `MissingResponseKeyException` (#5)

### Changed
- Add `phpdocumentor/reflection-docblock` (`^5.3 || ^6.0`) as a runtime dependency, used to resolve entity types from `@return` docblocks (#5)

The raw `array` / `string` / PSR-7 `MessageInterface` return paths are unchanged, so this release is backward compatible.

## [1.0.1] - 2026-06-25

### Changed
- Require stable `ray/media-query` (`^1.0`) instead of the pre-release constraint `^1.0.0-rc1` (#4)
- Raise the minimum PHP version to 8.2 to match `ray/media-query`'s stable requirement (#4)

### Fixed
- Correct the package name (`ray/web-query`) and the usage examples in the README

## [1.0.0] - 2026-06-24

First stable release.

### Changed
- Relax the `rize/uri-template` constraint to `^0.3 || ^0.4` so the package can be installed alongside packages that require `rize/uri-template ^0.4` such as `bear/resource` 1.29+ and `ray/aura-sql-module` 1.17.x (#2)

### Fixed
- Make the web query test suite hermetic so it no longer depends on a live external schema URL (#3)

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


[1.0.1]: https://github.com/ray-di/Ray.WebQuery/releases/tag/1.0.1
[1.0.0]: https://github.com/ray-di/Ray.WebQuery/releases/tag/1.0.0
[1.0.0-rc1]: https://github.com/ray-di/Ray.MediaQuery-Web/releases/tag/1.0.0-rc1