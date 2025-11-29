# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**typed-registry-laravel** is a Laravel integration package for [alexkart/typed-registry](https://github.com/alexkart/typed-registry). It provides type-safe helper functions and facades for Laravel's environment variables and configuration system, following Laravel best practices.

**Namespace:** `TypedRegistry\Laravel\`
**PHP Version:** ≥8.3
**Laravel Version:** ≥11.0, ≥12.0
**Quality Target:** PHPStan Level Max (10), zero errors, zero baseline

## Laravel Best Practices

**CRITICAL:** This package enforces Laravel best practices for environment variable access:

- ✅ Environment variables (`typedEnv()`) should ONLY be used in config files
- ❌ NEVER access environment variables directly in controllers, services, or other application code
- ✅ Application code should access configuration via `TypedConfig` facade or `typedConfig()` helper

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

### Core Components

1. **Providers** (`src/Providers/`)
   - `EnvProvider` - Wraps `Illuminate\Support\Env` with intelligent type casting
   - `EnvStringProvider` - Wraps `Illuminate\Support\Env`, casts all scalars to strings
   - `ConfigProvider` - Wraps Laravel's `Config` facade (strict, no casting)

2. **Facades** (`src/Facades/`)
   - `TypedConfig` - For accessing configuration anywhere in the application

3. **Helper Functions** (`src/helpers.php`)
   - `typedEnv()` - For use in config files only (with numeric casting)
   - `typedEnvString()` - For use in config files only (all scalars as strings)
   - `typedConfig()` - Alternative to TypedConfig facade

4. **Service Provider** (`src/TypedRegistryServiceProvider.php`)
   - Registers `typed-registry.config` singleton
   - Auto-discovered by Laravel

### Design Decisions

#### Why No TypedEnv Facade?

The `TypedEnv` facade was intentionally removed because:

1. **Laravel Best Practices**: Environment variables should only be accessed in config files
2. **Facades Don't Work in Config Files**: Config files load before facades are available
3. **Prevents Anti-Patterns**: Having a facade would encourage direct env access in controllers/services

Instead, use:
- **In config files**: `typedEnv()` helper function
- **In application code**: `TypedConfig` facade to access config values

#### Type Casting Strategy

**EnvProvider (with casting)**:
- Used by `typedEnv()` helper
- Automatically casts numeric strings (`"8080"` → `8080`)
- Handles scientific notation (`"1e3"` → `1000.0`)
- Manages edge cases (leading zeros, overflow, whitespace)
- Philosophy: Environment variables are strings in `.env`, casting makes them usable

**EnvStringProvider (cast to string)**:
- Used by `typedEnvString()` helper
- Casts all scalar values to strings (no numeric conversion)
- Useful for passwords, tokens, API keys that may be all-numeric
- Booleans: `true` → `"1"`, `false` → `""` (PHP native casting)
- Null stays null
- Philosophy: Some values are semantically strings even if they look like numbers

**ConfigProvider (no casting)**:
- Used by `TypedConfig` facade
- Strict type validation, zero coercion
- Philosophy: Config values have types in PHP arrays, preserve them

## Code Style

### General Rules

- **Always use strict types**: `declare(strict_types=1);` at the top of every PHP file
- **Final classes by default**: Use `final class` unless inheritance is explicitly needed
- **Exception**: Facades should NOT be final (Laravel IDE Helper extends them for autocomplete)
- **Explicit return types**: Every method must have a return type declaration
- **No mixed types**: Avoid `mixed` - use specific types or union types
- **PHPDoc for arrays**: Use array shape annotations when appropriate

### Example

```php
<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Providers;

use Illuminate\Support\Env;
use TypedRegistry\Provider;

final class EnvProvider implements Provider
{
    public function get(string $key): mixed
    {
        $value = Env::get($key);

        if (!is_string($value)) {
            return $value;
        }

        // Casting logic...
    }
}
```

## Testing Guidelines

### Test Organization

- **Unit tests** for providers in `tests/Providers/`
- **Integration tests** for service provider in `tests/Integration/`
- Use PHPUnit's test organization features

### Writing Tests

```php
public function testGetCastsIntegerStrings(): void
{
    putenv('TEST_VAR=123');
    $value = $this->provider->get('TEST_VAR');

    self::assertSame(123, $value);
    self::assertIsInt($value);
}
```

### Test Coverage Expectations

- All providers must have comprehensive test coverage
- Test edge cases (leading zeros, scientific notation, overflow, whitespace)
- Test both success and failure paths
- Integration tests for service provider registration

## EnvProvider Casting Rules

The `EnvProvider` implements intelligent type casting following these rules:

### Integer Casting
- String must be numeric: `is_numeric($value) === true`
- Must represent whole number: `(string)(int)$value === $value`
- Handles: negative numbers, leading zeros, leading plus, whitespace
- Example: `"042"` → `42`, `"-123"` → `-123`

### Float Casting
- String must be numeric AND contain `.`, `e`, or `E`
- Used for decimals and scientific notation
- Prevents integer overflow (values > PHP_INT_MAX become floats)
- Example: `"3.14"` → `3.14`, `"1e3"` → `1000.0`

### No Casting
- Non-numeric strings pass through unchanged
- Mixed alphanumeric strings remain strings
- Empty strings remain empty strings

### Laravel's Env Handles
- Boolean strings: `"true"` → `true`, `"false"` → `false`
- Null strings: `"null"` → `null`, `"(null)"` → `null`

## Common Tasks

### Adding a New Provider

1. Create provider class in `src/Providers/` implementing `TypedRegistry\Provider`
2. Add corresponding facade in `src/Facades/` (if needed)
3. Register in `TypedRegistryServiceProvider`
4. Write comprehensive tests in `tests/Providers/`
5. Update README with usage examples

### Updating Type Casting Logic

1. Modify `EnvProvider::get()` method
2. Add test cases to `EnvProviderTest`
3. Run PHPStan to ensure type safety
4. Update README type casting documentation
5. Consider edge cases (overflow, whitespace, special characters)

## PHPStan Configuration

- **Level**: Max (10)
- **Strict rules**: Enabled via `phpstan/phpstan-strict-rules`
- **Baseline**: None allowed (must be zero errors)
- **Key checks**:
  - No mixed types
  - Strict array shapes
  - All properties typed
  - All method parameters and returns typed

## Package Dependencies

- `alexkart/typed-registry` (^0.1) - Core TypedRegistry functionality
- `illuminate/support` (^11.0|^12.0) - Laravel Env and helpers
- `illuminate/contracts` (^11.0|^12.0) - Laravel contracts

## Publishing Workflow

1. Update `CHANGELOG.md` with changes
2. Ensure all tests pass: `composer test`
3. Ensure PHPStan passes: `composer phpstan`
4. Update version in README if needed
5. Tag release following semver

## Questions or Issues?

- Check existing issues: https://github.com/alexkart/typed-registry-laravel/issues
- Review parent package docs: https://github.com/alexkart/typed-registry
- Laravel configuration best practices: https://laravel.com/docs/configuration

## Key Files

- `src/Providers/EnvProvider.php` - Environment variable provider with intelligent casting
- `src/Providers/EnvStringProvider.php` - Environment variable provider that casts all scalars to strings
- `src/Providers/ConfigProvider.php` - Configuration provider (strict, no casting)
- `src/Facades/TypedConfig.php` - Configuration facade
- `src/helpers.php` - Global helper functions
- `src/TypedRegistryServiceProvider.php` - Laravel service provider
- `tests/Providers/EnvProviderTest.php` - Comprehensive EnvProvider tests
- `tests/Providers/EnvStringProviderTest.php` - Comprehensive EnvStringProvider tests
- `README.md` - User-facing documentation

## Remember

- Environment variables are accessed via `typedEnv()` or `typedEnvString()` in config files ONLY
- Use `typedEnvString()` for values that must stay strings (passwords, tokens, API keys)
- Application code uses `TypedConfig` facade or `typedConfig()` helper to access config
- No `TypedEnv` facade exists - this is intentional to enforce Laravel best practices
- All casting happens in providers, facades and helpers just expose TypedRegistry methods
