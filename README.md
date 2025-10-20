# typed-registry-laravel

Laravel integration for [typed-registry](https://github.com/alexkart/typed-registry) following Laravel best practices for environment variable and configuration access.

[![Tests](https://github.com/alexkart/typed-registry-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/alexkart/typed-registry-laravel/actions)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-max-blue.svg)](https://phpstan.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## Why?

Laravel's `env()` and `config()` helpers return `mixed` values, making strict type checking difficult. This package provides:

- **Type-safe config access** - `TypedConfig` facade and `typedConfig()` helper with strict return types
- **Type-safe env access** - `typedEnv()` helper for use in config files only (following Laravel best practices)
- **Intelligent casting** - Environment variables automatically cast from strings (`"8080"` → `8080`, `"1e3"` → `1000.0`)
- **PHPStan ready** - Works seamlessly with static analysis at max level
- **Zero runtime overhead** - Simple wrappers around Laravel's existing systems

## Installation

```bash
composer require alexkart/typed-registry-laravel
```

Requires:
- PHP 8.3+
- Laravel 11+ or 12+

The package uses Laravel's auto-discovery feature. The service provider and facade are registered automatically.

## Quick Start

### Environment Variables in Config Files

**Following Laravel Best Practices:** Environment variables should ONLY be accessed in config files, never directly in controllers or services.

```php
// config/app.php
return [
    'name' => typedEnv()->getStringOr('APP_NAME', 'Laravel'),
    'debug' => typedEnv()->getBoolOr('APP_DEBUG', false),
    'port' => typedEnv()->getIntOr('APP_PORT', 8080),          // "8080" → 8080
    'timeout' => typedEnv()->getFloatOr('TIMEOUT', 2.5),       // "2.5" → 2.5
    'max_items' => typedEnv()->getInt('MAX_ITEMS'),            // Throws if missing
];
```

### Configuration Access Everywhere

Use the `TypedConfig` facade or `typedConfig()` helper in controllers, services, and anywhere else:

```php
use TypedRegistry\Laravel\Facades\TypedConfig;

class UserController
{
    public function index()
    {
        $perPage = TypedConfig::getInt('app.pagination.per_page');
        $appName = TypedConfig::getString('app.name');
        $features = TypedConfig::getStringList('app.enabled_features');

        // Or use the helper
        $timeout = typedConfig()->getFloat('app.timeout');
    }
}
```

## Laravel Best Practices

### ✅ Correct: Environment Variables

```php
// ✅ In config files ONLY
// config/database.php
return [
    'host' => typedEnv()->getStringOr('DB_HOST', '127.0.0.1'),
    'port' => typedEnv()->getIntOr('DB_PORT', 3306),
];
```

### ❌ Wrong: Direct env() in Controllers/Services

```php
// ❌ NEVER do this - violates Laravel best practices
class UserController
{
    public function index()
    {
        $host = env('DB_HOST');  // ❌ Wrong!
        $port = typedEnv()->getInt('DB_PORT'); // ❌ Still wrong!
    }
}
```

### ✅ Correct: Use Config Instead

```php
// ✅ Correct - access config, not env
class UserController
{
    public function index()
    {
        $host = TypedConfig::getString('database.connections.mysql.host');
        $port = TypedConfig::getInt('database.connections.mysql.port');
    }
}
```

## Features

### `typedEnv()` Helper - For Config Files Only

Wraps `Illuminate\Support\Env` with intelligent type casting for numeric strings:

```php
// config/app.php
return [
    // Automatic type casting from .env strings:
    'port' => typedEnv()->getInt('PORT'),           // "8080" → int(8080)
    'rate' => typedEnv()->getFloat('RATE'),         // "2.5" → float(2.5)
    'limit' => typedEnv()->getFloat('LIMIT'),       // "1e3" → float(1000.0)
    'debug' => typedEnv()->getBool('APP_DEBUG'),    // "true" → bool(true)

    // With defaults (never throws):
    'name' => typedEnv()->getStringOr('APP_NAME', 'Laravel'),
    'timeout' => typedEnv()->getFloatOr('TIMEOUT', 30.0),
];
```

**Casting Rules:**
- Numeric strings → `int` or `float` based on format (handles scientific notation, leading zeros, whitespace, overflow)
- Boolean strings (`"true"`, `"false"`) → `bool` (handled by Laravel's `Env`)
- Null strings (`"null"`, `"(null)"`) → `null` (handled by Laravel's `Env`)
- All other strings remain unchanged

### `TypedConfig` Facade - Use Anywhere

Wraps Laravel's `Config` facade with **strict typing, no casting**:

```php
use TypedRegistry\Laravel\Facades\TypedConfig;

// In controllers, services, jobs, etc.
$driver = TypedConfig::getString('database.default');
$port = TypedConfig::getInt('database.connections.mysql.port');
$options = TypedConfig::getStringMap('database.connections.mysql.options');

// With defaults:
$perPage = TypedConfig::getIntOr('app.pagination.per_page', 15);
```

Or use the helper function:

```php
$driver = typedConfig()->getString('database.default');
```

## Full API

Both `typedEnv()` and `TypedConfig` expose the same 20 methods from `TypedRegistry`:

### Primitive Getters

```php
->getString('KEY');   // string - throws if missing/wrong type
->getInt('KEY');      // int
->getBool('KEY');     // bool
->getFloat('KEY');    // float
```

### Nullable Variants

```php
->getNullableString('KEY');  // string|null
->getNullableInt('KEY');     // int|null
->getNullableBool('KEY');    // bool|null
->getNullableFloat('KEY');   // float|null
```

### With Defaults (Never Throws)

```php
->getStringOr('KEY', 'default');  // Returns default if missing/wrong type
->getIntOr('KEY', 8080);
->getBoolOr('KEY', false);
->getFloatOr('KEY', 1.5);
```

### Lists (Sequential Arrays)

```php
->getStringList('KEY');  // list<string>
->getIntList('KEY');     // list<int>
->getBoolList('KEY');    // list<bool>
->getFloatList('KEY');   // list<float>
```

### Maps (Associative Arrays with String Keys)

```php
->getStringMap('KEY');  // array<string, string>
->getIntMap('KEY');     // array<string, int>
->getBoolMap('KEY');    // array<string, bool>
->getFloatMap('KEY');   // array<string, float>
```

## Type Casting Behavior

### EnvProvider - Intelligent Casting

The `EnvProvider` (used by `typedEnv()`) intelligently casts numeric environment variable strings:

```php
// Integer casting (handles edge cases)
"123"    → int(123)
"-456"   → int(-456)
"0"      → int(0)
"042"    → int(42)     // Leading zeros removed
"-042"   → int(-42)    // Negative with leading zeros
"+42"    → int(42)     // Leading plus removed
" 042 "  → int(42)     // Whitespace trimmed

// Integer overflow protection (values exceeding PHP_INT_MAX/MIN)
"9223372036854775808"  → float(9.223372036854776E+18)  // Too large for int
"-9223372036854775809" → float(-9.223372036854776E+18) // Too small for int

// Float casting (decimal point or scientific notation)
"3.14"   → float(3.14)
"0.0"    → float(0.0)
"1e3"    → float(1000.0)        // Scientific notation
"2.5e-4" → float(0.00025)       // Scientific with decimal
"1E10"   → float(10000000000.0) // Uppercase E
"042.5"  → float(42.5)

// No casting
"Laravel"  → "Laravel"  // Non-numeric
"123abc"   → "123abc"   // Mixed alphanumeric
""         → ""         // Empty string

// Laravel's Env handles these:
"true"     → bool(true)
"false"    → bool(false)
"null"     → null
"(null)"   → null
```

### ConfigProvider - No Casting

ConfigProvider performs **zero type coercion**. Values must be stored with the correct type:

```php
// config/app.php
return [
    'port' => 8080,        // ✅ int - TypedConfig::getInt() works
    'port_str' => '8080',  // ❌ string - TypedConfig::getInt() throws
];
```

## Error Handling

### Strict Getters Throw on Type Mismatch

```php
use TypedRegistry\RegistryTypeError;

try {
    $port = TypedConfig::getInt('app.name'); // If 'app.name' is a string
} catch (RegistryTypeError $e) {
    // "[typed-registry] key 'app.name' must be int, got 'Laravel'"
}
```

### Default Getters Never Throw

```php
// Returns default value on missing key OR type mismatch
$port = typedEnv()->getIntOr('NONEXISTENT_PORT', 8080);  // 8080
$timeout = TypedConfig::getFloatOr('cache.timeout', 3.0); // 3.0
```

## Real-World Example

```php
// config/app.php
return [
    'name' => typedEnv()->getStringOr('APP_NAME', 'Laravel'),
    'env' => typedEnv()->getStringOr('APP_ENV', 'production'),
    'debug' => typedEnv()->getBoolOr('APP_DEBUG', false),
    'url' => typedEnv()->getStringOr('APP_URL', 'http://localhost'),

    'timezone' => 'UTC',

    'locale' => typedEnv()->getStringOr('APP_LOCALE', 'en'),

    'providers' => [
        // Service providers...
    ],
];
```

```php
// app/Http/Controllers/DashboardController.php
use TypedRegistry\Laravel\Facades\TypedConfig;

class DashboardController extends Controller
{
    public function index()
    {
        $appName = TypedConfig::getString('app.name');
        $isDebug = TypedConfig::getBool('app.debug');
        $locale = TypedConfig::getString('app.locale');

        return view('dashboard', compact('appName', 'isDebug', 'locale'));
    }
}
```

## PHPStan Integration

The package works seamlessly with PHPStan at max level:

```php
/** @var int $port */
$port = TypedConfig::getInt('app.port'); // PHPStan knows this is int

/** @var list<string> $hosts */
$hosts = TypedConfig::getStringList('app.hosts'); // PHPStan knows the shape
```

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test
# or: vendor/bin/phpunit

# Run static analysis
composer phpstan
# or: vendor/bin/phpstan analyse
```

**Quality Standards:**
- PHPStan Level: Max (10) with strict rules
- Test Coverage: All providers and facades
- PHP Version: 8.3+
- Laravel Version: 11+, 12+

## Comparison with Core Package

| Feature | `alexkart/typed-registry` | `alexkart/typed-registry-laravel` |
|---------|---------------------------|-----------------------------------|
| Framework | Framework-agnostic | Laravel-specific |
| Type Casting | None (strict only) | `EnvProvider` casts numeric strings |
| Facades | No | `TypedConfig` |
| Helper Functions | No | `typedEnv()`, `typedConfig()` |
| Auto-discovery | N/A | Yes |
| Laravel Best Practices | N/A | Enforced (env only in config) |

## Contributing

Contributions are welcome! Please ensure:

1. All tests pass (`vendor/bin/phpunit`)
2. PHPStan Level 10 passes (`vendor/bin/phpstan analyse`)
3. Code follows existing style (strict types, final classes)

## License

MIT License. See [LICENSE](LICENSE) for details.

## Credits

- Built on [alexkart/typed-registry](https://github.com/alexkart/typed-registry)
- Maintained by the TypedRegistry contributors

---

**Questions?** Open an issue on [GitHub](https://github.com/alexkart/typed-registry-laravel/issues).
