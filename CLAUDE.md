# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**typed-registry-laravel** is a Laravel integration package for [alexkart/typed-registry](https://github.com/alexkart/typed-registry). It provides type-safe facades and providers for Laravel's environment variables and configuration system, with intelligent type casting where appropriate.

**Namespace:** `TypedRegistry\Laravel\`
**PHP Version:** ≥8.3
**Laravel Version:** ≥11.0
**Quality Target:** PHPStan Level Max (10), zero errors, zero baseline

## Development Commands

### Testing
```bash
# Run all tests
vendor/bin/phpunit

# Run tests with detailed output
vendor/bin/phpunit --testdox

# Run specific test class
vendor/bin/phpunit tests/Providers/EnvProviderTest.php

# Run specific test method
vendor/bin/phpunit --filter testGetCastsIntegerStrings
```

### Static Analysis
```bash
# Run PHPStan at max level
vendor/bin/phpstan analyse

# PHPStan config is in phpstan.neon.dist
# Local overrides go in phpstan.neon (gitignored)
```

### Setup
```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

## Architecture

### Core Principle: Two Specific Facades, No Generic Facade

This package provides **two separate facades** for two distinct use cases:

1. **TypedEnv** - Environment variables with intelligent type casting
2. **TypedConfig** - Configuration repository with strict validation (no casting)

**Important:** There is NO generic `TypedRegistry` facade. This design decision ensures clarity - users always know exactly where their data is coming from.

### Provider Pattern

The package follows the same Provider pattern as the core `typed-registry` package:

```
Laravel System → Provider → TypedRegistry → Typed values
```

### Built-in Providers

1. **EnvProvider** - Wraps `Illuminate\Support\Env` with numeric type casting
2. **ConfigProvider** - Wraps `Illuminate\Support\Facades\Config` (strict, no casting)

### Facades

Both facades extend `Illuminate\Support\Facades\Facade`:

- **TypedEnv** → Container binding: `'typed-registry.env'`
- **TypedConfig** → Container binding: `'typed-registry.config'`

### Service Provider

**TypedRegistryServiceProvider** implements `DeferrableProvider` and registers:
- `'typed-registry.env'` → Singleton with EnvProvider
- `'typed-registry.config'` → Singleton with ConfigProvider

Auto-discovery is configured in `composer.json` under `extra.laravel`.

## Type Casting Philosophy

### EnvProvider: Intelligent Casting

**Why casting?** Environment variables are inherently strings. Laravel's `Env::get()` already handles booleans and nulls, but numeric values remain strings. EnvProvider extends this pattern.

**Casting rules:**
- Numeric strings → `int` if whole number (e.g., `"123"` → `123`)
- Numeric strings → `float` if decimal/scientific (e.g., `"3.14"` → `3.14`, `"1e3"` → `1000.0`)
- Non-numeric strings → unchanged (e.g., `"Laravel"` stays `"Laravel"`)
- Booleans/nulls → handled by `Env::get()` (e.g., `"true"` → `true`, `"null"` → `null`)

**Implementation detail:**
```php
if (!is_numeric($value)) return $value;
if (strpbrk($value, '.eE') !== false) return (float)$value;  // Has decimal or exponent
return (int)$value;  // Whole number (handles leading zeros, plus signs)
```

This ensures:
- `"123"` → `int(123)`
- `"042"` → `int(42)` (leading zeros handled)
- `"+42"` → `int(42)` (leading plus handled)
- `"123.0"` → `float(123.0)` (has decimal point)
- `"1e3"` → `float(1000.0)` (scientific notation)

### ConfigProvider: Zero Casting

**Why no casting?** Configuration values are PHP arrays with proper types. Coercion would hide bugs.

**Uses `Config::get()` facade directly** - no constructor injection needed, consistent with EnvProvider pattern.

**Example:**
```php
// config/app.php
return ['port' => 8080]; // Already an int

TypedConfig::getInt('app.port'); // ✅ Works
```

If config has `'port' => '8080'` (string), `TypedConfig::getInt()` will **throw** - this is intentional and correct.

## Code Style & Conventions

### Required Patterns
- **Strict types:** Every file starts with `declare(strict_types=1);`
- **Final classes:** All concrete classes are `final`
- **Readonly when possible:** Use readonly properties or private with no setters
- **Explicit return types:** Every method has a return type (including `void`)
- **PHPDoc for arrays:** Use `@var` annotations for PHPStan

### Forbidden Patterns
- **No coercion in ConfigProvider:** Only EnvProvider performs casting
- **No inheritance:** No abstract classes, traits, or class hierarchies
- **No nullable by default:** Only use nullable when `null` has meaning
- **No generic facade:** Only specific facades (TypedEnv, TypedConfig)

### Namespace Conventions
- Providers: `TypedRegistry\Laravel\Providers\`
- Facades: `TypedRegistry\Laravel\Facades\`
- Service Provider: `TypedRegistry\Laravel\TypedRegistryServiceProvider`
- Tests: `TypedRegistry\Laravel\Tests\`

## Testing Strategy

### Test Organization

1. **tests/Providers/EnvProviderTest.php** - 24 tests
   - Type casting: integers, floats, scientific notation
   - Pass-through: non-numeric strings, empty strings
   - Laravel Env integration: booleans, nulls
   - Integration with TypedRegistry

2. **tests/Providers/ConfigProviderTest.php** - 15 tests
   - Dot-notation support
   - Strict validation (no coercion)
   - Integration with TypedRegistry
   - Array/list/map handling

3. **tests/Integration/ServiceProviderTest.php** - 14 tests
   - Container bindings
   - Facade functionality
   - Singleton behavior
   - Deferrable provider

### Test Coverage Requirements
- Every provider method has happy path tests
- Type mismatch tests for strict validation
- Casting behavior tests for all numeric formats
- Facade integration tests
- Service provider registration tests

### Testing with Orchestra Testbench
All tests extend `Orchestra\Testbench\TestCase` to simulate a Laravel environment:

```php
protected function getPackageProviders($app): array
{
    return [TypedRegistryServiceProvider::class];
}

protected function getPackageAliases($app): array
{
    return [
        'TypedEnv' => TypedEnv::class,
        'TypedConfig' => TypedConfig::class,
    ];
}
```

### Running Focused Tests
```bash
# Test only EnvProvider
vendor/bin/phpunit --filter EnvProvider

# Test only type casting
vendor/bin/phpunit --filter Casts

# Test only facades
vendor/bin/phpunit --filter Facade
```

## PHPStan Considerations

### Facade Type Hints

Both facades use `@method` annotations for IDE support:

```php
/**
 * @method static string getString(string $key)
 * @method static int getInt(string $key)
 * ...
 */
final class TypedEnv extends Facade
```

These must stay in sync with `TypedRegistry` public API.

### Import Statements
Follow core package pattern - no function imports needed since providers are simple wrappers.

## Design Philosophy

### What This Package Does
- Provides Laravel-specific integration for typed-registry
- Intelligent type casting for environment variables (user expectation)
- Strict validation for config values (catches bugs)
- Convenient facades for common Laravel patterns
- Auto-discovery for zero-config setup

### What This Package Doesn't Do
- **Custom validation rules** - Use typed-registry core or validation package
- **Schema validation** - Future separate package
- **Support older Laravel versions** - Laravel 11+ only for simplicity
- **Provide a generic facade** - Intentionally avoided for clarity

### When to Cast vs. Strict
- **EnvProvider (cast):** Environment variables are strings by nature, casting is helpful
- **ConfigProvider (strict):** Config files are PHP, they should have correct types

## Dependencies & Compatibility

### Production Dependencies
- `alexkart/typed-registry: ^0.1` - Core package
- `illuminate/support: ^11.0` - Laravel's Env and Config facades
- `illuminate/contracts: ^11.0` - Laravel contracts for service provider integration

### Development Dependencies
- `orchestra/testbench: ^9.0` - Laravel testing harness
- `phpunit/phpunit: ^11.0` - Testing framework
- `phpstan/phpstan: ^2.0` - Static analysis
- `phpstan/phpstan-strict-rules: ^2.0` - Extra strict rules

### Version Policy
- **Laravel:** Major version only (^11.0) - follow Laravel's version scheme
- **PHP:** ^8.3 - matches core package, enables modern features
- **Core package:** Follow core package versioning (currently ^0.1)

## Updating When Core Package Changes

### If Core Package Adds New Method

1. Update facade `@method` annotations in both `TypedEnv` and `TypedConfig`
2. No code changes needed (facades proxy to TypedRegistry)
3. Add integration tests if behavior differs between providers
4. Update README with examples

### If Core Package Changes API (Breaking)

1. Bump major version of this package too
2. Update all facade annotations
3. Update tests
4. Update CHANGELOG.md with migration guide

### If Core Package Bumps to 1.0

1. Update `composer.json`: `"alexkart/typed-registry": "^1.0"`
2. Test everything still works
3. Tag matching version (e.g., if core is 1.0, tag this as 1.0)

## Laravel Auto-Discovery

The package is automatically discovered via `composer.json`:

```json
{
  "extra": {
    "laravel": {
      "providers": [
        "TypedRegistry\\Laravel\\TypedRegistryServiceProvider"
      ],
      "aliases": {
        "TypedEnv": "TypedRegistry\\Laravel\\Facades\\TypedEnv",
        "TypedConfig": "TypedRegistry\\Laravel\\Facades\\TypedConfig"
      }
    }
  }
}
```

Users never need to manually register the service provider or facades.

## CI/CD

### GitHub Actions Workflow

Located in `.github/workflows/tests.yml`:

- Runs on: `push` and `pull_request` to `master`/`main`
- Matrix: PHP 8.3, 8.4 × Laravel 11
- Steps:
  1. Checkout code
  2. Setup PHP with extensions
  3. Install dependencies (matching matrix versions)
  4. Run PHPUnit
  5. Run PHPStan

### Quality Gates

All of these must pass:
- ✅ PHPUnit tests (no failures)
- ✅ PHPStan Level Max (zero errors)
- ✅ No baseline files

## Common Development Tasks

### Adding a New Provider

1. Create in `src/Providers/NewProvider.php`
2. Implement `TypedRegistry\Provider` interface
3. Add to service provider registration
4. Create facade in `src/Facades/TypedNew.php`
5. Add tests in `tests/Providers/NewProviderTest.php`
6. Update README with examples
7. Add to CHANGELOG.md

### Fixing a Bug

1. Write a failing test that reproduces the bug
2. Fix the code
3. Verify test passes
4. Run full test suite (`vendor/bin/phpunit`)
5. Run PHPStan (`vendor/bin/phpstan analyse`)
6. Update CHANGELOG.md under "Unreleased"

### Preparing a Release

1. Update CHANGELOG.md (move "Unreleased" to version number)
2. Update version number in README badges if needed
3. Commit: `git commit -m "Release vX.Y.Z"`
4. Tag: `git tag vX.Y.Z`
5. Push: `git push && git push --tags`
6. Packagist will auto-update via webhook

## Troubleshooting

### Tests Failing: "Class 'Illuminate\Support\Env' not found"

Orchestra Testbench is missing. Run:
```bash
composer require --dev orchestra/testbench
```

### PHPStan Error: "Method ... has no return type"

Add explicit return type. Even `void` must be specified:
```php
public function register(): void // ✅ Explicit
public function register()       // ❌ Implicit
```

### Facades Not Working in Tests

Make sure you're extending `Orchestra\Testbench\TestCase` and implementing:
```php
protected function getPackageProviders($app): array
{
    return [TypedRegistryServiceProvider::class];
}
```

### Type Casting Not Working as Expected

Check the order of operations in `EnvProvider::get()`:
1. Laravel's `Env::get()` handles booleans/nulls first
2. Then numeric casting applies
3. Non-numeric strings pass through unchanged

## File Structure Reference

```
typed-registry-laravel/
├── src/
│   ├── Facades/
│   │   ├── TypedConfig.php         # Config facade (strict)
│   │   └── TypedEnv.php            # Env facade (with casting)
│   ├── Providers/
│   │   ├── ConfigProvider.php      # Laravel config wrapper
│   │   └── EnvProvider.php         # Laravel Env wrapper + casting
│   └── TypedRegistryServiceProvider.php
├── tests/
│   ├── Integration/
│   │   └── ServiceProviderTest.php # Facade & DI tests
│   └── Providers/
│       ├── ConfigProviderTest.php  # Config provider tests
│       └── EnvProviderTest.php     # Env provider + casting tests
├── .github/workflows/tests.yml     # CI configuration
├── CHANGELOG.md                    # Version history
├── composer.json                   # Package definition
├── LICENSE                         # MIT license
├── phpstan.neon.dist              # Static analysis config
├── phpunit.xml.dist               # Test configuration
└── README.md                       # User documentation
```

## Contributing Guidelines

When accepting contributions:

1. **Code must pass CI** - All tests and PHPStan checks
2. **Follow existing patterns** - Same style as core package
3. **Add tests for new features** - 100% coverage target
4. **Update documentation** - README and inline PHPDoc
5. **Update CHANGELOG** - Under "Unreleased" section
6. **No breaking changes in minor versions** - Follow SemVer strictly

## Support & Maintenance

This package should remain simple and focused:
- Keep in sync with core package API
- Update Laravel/PHP version constraints as needed
- Add providers only when there's clear user demand
- Reject features that belong in core package or separate packages

**Philosophy:** This is a thin integration layer, not a feature-rich framework. Keep it small, fast, and maintainable.
