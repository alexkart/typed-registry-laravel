# Changelog

All notable changes to `typed-registry-laravel` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
While the package is pre-1.0, `0.x` minor releases may include breaking changes.

## [Unreleased]

### Fixed
- `EnvProvider` now trims the form-feed byte (`\f`, `0x0C`) that `is_numeric()` accepts as
  surrounding whitespace. Previously a form-feed-padded integer string (e.g. `"\f8080"`) was
  cast to `float` instead of `int`, which made `getInt()` throw and `getIntOr()` silently
  return the default instead of the parsed value.

## [0.5.0] - 2026-03-22

### Added
- Support for Laravel 13: `illuminate/support` and `illuminate/contracts` `^13.0`, and
  Orchestra Testbench `^11.0`. The CI matrix now exercises Laravel 11, 12, and 13.

## [0.4.0] - 2025-11-30

### Added
- `EnvStringProvider` and the `typedEnvString()` helper for string-preserving env access.
  All scalar env values are cast to strings, so all-numeric secrets (passwords, tokens,
  API keys) are returned verbatim instead of being coerced to `int`/`float` by `typedEnv()`.

### Changed
- CI now also tests Laravel 12 with Testbench `^10.0`.
- Internal test cleanup following the migration from the `TypedEnv` facade to the `typedEnv()` helper.

### Removed
- Dropped the "Laravel best practices" prose section from the README (the guidance is retained
  in the code documentation and `CLAUDE.md`).

## [0.3.0] - 2025-10-21

### Changed
- `TypedConfig` facade is no longer `final`, allowing the Laravel IDE Helper (and subclasses)
  to extend it for autocompletion.

## [0.2.0] - 2025-10-21

### Added
- `typedEnv()` and `typedConfig()` helper functions for type-safe environment and configuration
  access without a facade (usable in config files, where facades are not yet available).
- Laravel 12 compatibility: `illuminate/support` and `illuminate/contracts` `^11.0|^12.0`,
  Orchestra Testbench `^10.0`.

### Removed
- **Breaking:** removed the `TypedEnv` facade. Read environment variables with the `typedEnv()`
  helper in config files only — facades are unavailable while config files load, and accessing
  env vars outside config files is discouraged by Laravel best practices.

## [0.1.0] - 2025-10-20

### Added
- Initial release.
- `EnvProvider` — environment variable provider with intelligent numeric casting: numeric
  strings to `int`/`float`, handling leading zeros, a leading `+`, surrounding whitespace,
  scientific notation, and integer-overflow promotion to `float`. Booleans and nulls are
  handled by Laravel's `Env`.
- `ConfigProvider` — strict configuration provider (zero coercion) backed by the `Config`
  facade, with dot-notation support for nested values.
- `TypedConfig` and `TypedEnv` facades for type-safe configuration and environment access.
- `TypedRegistryServiceProvider` with Laravel package auto-discovery.

### Requirements
- PHP `^8.3`
- Laravel `^11.0`
- `alexkart/typed-registry` `^0.1`

[Unreleased]: https://github.com/alexkart/typed-registry-laravel/compare/0.5.0...HEAD
[0.5.0]: https://github.com/alexkart/typed-registry-laravel/releases/tag/0.5.0
[0.4.0]: https://github.com/alexkart/typed-registry-laravel/releases/tag/0.4.0
[0.3.0]: https://github.com/alexkart/typed-registry-laravel/releases/tag/0.3.0
[0.2.0]: https://github.com/alexkart/typed-registry-laravel/releases/tag/0.2.0
[0.1.0]: https://github.com/alexkart/typed-registry-laravel/releases/tag/0.1.0
