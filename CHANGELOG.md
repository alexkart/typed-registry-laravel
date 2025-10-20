# Changelog

All notable changes to `typed-registry-laravel` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-XX

### Added
- Initial release of typed-registry-laravel package
- `EnvProvider` - Laravel environment variable provider with intelligent type casting
  - Automatically casts numeric strings to `int` and `float`
  - Preserves Laravel's `Env` handling of booleans and nulls
- `ConfigProvider` - Laravel configuration repository provider (strict, no type casting)
  - Supports dot-notation for nested config access
  - Zero coercion, strict type validation only
- `TypedEnv` facade for type-safe environment variable access
- `TypedConfig` facade for type-safe configuration access
- `TypedRegistryServiceProvider` with Laravel auto-discovery support
- Full test suite with 100% coverage
  - EnvProvider tests (24 test cases)
  - ConfigProvider tests (15 test cases)
  - ServiceProvider integration tests (14 test cases)
- PHPStan Level Max compliance with strict rules
- GitHub Actions CI for PHP 8.3, 8.4 with Laravel 11
- Comprehensive documentation and usage examples

### Requirements
- PHP ^8.3
- Laravel ^11.0
- alexkart/typed-registry ^1.0

[1.0.0]: https://github.com/alexkart/typed-registry-laravel/releases/tag/v1.0.0
