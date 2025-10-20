# typed-registry-laravel

Laravel integration for [typed-registry](https://github.com/alexkart/typed-registry) with type-casting providers and convenient facades.

[![Tests](https://github.com/alexkart/typed-registry-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/alexkart/typed-registry-laravel/actions)
[![PHPStan Level](https://img.shields.io/badge/PHPStan-max-blue.svg)](https://phpstan.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## Why?

Laravel's `env()` and `config()` helpers return `mixed` values, making strict type checking difficult. This package provides:

- **Type-safe facades** - `TypedEnv` and `TypedConfig` with strict return types
- **Intelligent casting** - Environment variables automatically cast from strings (`"8080"` → `8080`)
- **PHPStan ready** - Works seamlessly with static analysis at max level
- **Zero runtime overhead** - Simple wrappers around Laravel's existing systems

## Installation

```bash
composer require alexkart/typed-registry-laravel
```

Requires:
- PHP 8.3+
- Laravel 11+

The package uses Laravel's auto-discovery feature. The service provider and facades are registered automatically.

## Quick Start

### Environment Variables (with Type Casting)

```php
use TypedRegistry\Laravel\Facades\TypedEnv;

// .env file:
// APP_DEBUG=true
// APP_PORT=8080
// APP_TIMEOUT=2.5
// APP_NAME=Laravel

$debug = TypedEnv::getBool('APP_DEBUG');       // bool(true)
$port = TypedEnv::getInt('APP_PORT');          // int(8080) - cast from "8080"
$timeout = TypedEnv::getFloat('APP_TIMEOUT');  // float(2.5) - cast from "2.5"
$name = TypedEnv::getString('APP_NAME');       // string("Laravel")
```

### Configuration Values (Strict, No Casting)

```php
use TypedRegistry\Laravel\Facades\TypedConfig;

// config/database.php
$driver = TypedConfig::getString('database.default');
$port = TypedConfig::getInt('database.connections.mysql.port');
$options = TypedConfig::getStringMap('database.connections.mysql.options');
```

## Features

### Two Facades for Two Use Cases

#### `TypedEnv` - Environment Variables with Casting

Wraps `Illuminate\Support\Env` and automatically casts numeric strings:

```php
// Automatic type casting:
TypedEnv::getInt('PORT');    // "8080" → int(8080)
TypedEnv::getFloat('RATE');  // "2.5" → float(2.5)
TypedEnv::getBool('DEBUG');  // "true" → bool(true)

// Non-numeric strings pass through:
TypedEnv::getString('APP_NAME'); // "Laravel" → "Laravel"
```

**Casting Rules:**
- Numeric strings → `int` or `float` based on format
- Boolean strings (`"true"`, `"false"`) → `bool` (handled by Laravel's `Env`)
- Null strings (`"null"`, `"(null)"`) → `null` (handled by Laravel's `Env`)
- All other strings remain unchanged

#### `TypedConfig` - Configuration Repository (Strict)

Wraps Laravel's config repository with **no type casting**:

```php
// Values must be exactly the expected type
TypedConfig::getInt('app.port');  // ✅ Works if config has int(8080)
TypedConfig::getInt('app.port');  // ❌ Throws if config has string("8080")

// Supports dot notation
TypedConfig::getString('database.connections.mysql.host');
```

### Full API

Both facades expose the same 20 methods from `TypedRegistry`:

#### Primitive Getters

```php
TypedEnv::getString('KEY');   // string
TypedEnv::getInt('KEY');      // int
TypedEnv::getBool('KEY');     // bool
TypedEnv::getFloat('KEY');    // float
```

#### Nullable Variants

```php
TypedEnv::getNullableString('KEY');  // string|null
TypedEnv::getNullableInt('KEY');     // int|null
TypedEnv::getNullableBool('KEY');    // bool|null
TypedEnv::getNullableFloat('KEY');   // float|null
```

#### With Defaults (Never Throws)

```php
TypedEnv::getStringOr('KEY', 'default');  // Returns default if missing/wrong type
TypedEnv::getIntOr('KEY', 8080);
TypedEnv::getBoolOr('KEY', false);
TypedEnv::getFloatOr('KEY', 1.5);
```

#### Lists (Sequential Arrays)

```php
TypedEnv::getStringList('KEY');  // list<string>
TypedEnv::getIntList('KEY');     // list<int>
TypedEnv::getBoolList('KEY');    // list<bool>
TypedEnv::getFloatList('KEY');   // list<float>
```

#### Maps (Associative Arrays with String Keys)

```php
TypedConfig::getStringMap('KEY');  // array<string, string>
TypedConfig::getIntMap('KEY');     // array<string, int>
TypedConfig::getBoolMap('KEY');    // array<string, bool>
TypedConfig::getFloatMap('KEY');   // array<string, float>
```

## Usage Examples

### Dependency Injection

```php
use TypedRegistry\TypedRegistry;

class MyService
{
    public function __construct(
        private TypedRegistry $env,
        private TypedRegistry $config
    ) {}

    public function getSettings(): array
    {
        return [
            'port' => $this->env->getInt('APP_PORT'),
            'database' => $this->config->getString('database.default'),
        ];
    }
}

// In a service provider:
app()->bind(MyService::class, function ($app) {
    return new MyService(
        $app->make('typed-registry.env'),
        $app->make('typed-registry.config')
    );
});
```

### Custom Providers

You can still use typed-registry's core providers directly:

```php
use TypedRegistry\TypedRegistry;
use TypedRegistry\Laravel\Providers\EnvProvider;
use TypedRegistry\Laravel\Providers\ConfigProvider;

// Manual instantiation
$env = new TypedRegistry(new EnvProvider());
$config = new TypedRegistry(new ConfigProvider($app->make('config')));

// Or resolve from container
$env = app('typed-registry.env');
$config = app('typed-registry.config');
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
$port = TypedEnv::getIntOr('NONEXISTENT_PORT', 8080); // 8080
$timeout = TypedConfig::getFloatOr('cache.timeout', 3.0); // 3.0
```

## Type Casting Behavior

### EnvProvider Casting Logic

The `EnvProvider` intelligently casts numeric environment variable strings:

```php
// Integer casting
"123"    → int(123)
"-456"   → int(-456)
"0"      → int(0)

// Float casting
"3.14"   → float(3.14)
"0.0"    → float(0.0)
"1e3"    → float(1000.0)
"2.5e-4" → float(0.00025)

// No casting
"Laravel"  → "Laravel" (non-numeric)
"123abc"   → "123abc" (mixed alphanumeric)
""         → "" (empty string)

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
    'port' => 8080,        // ✅ int
    'port_str' => '8080',  // ❌ string (TypedConfig::getInt() throws)
];
```

## Comparison

### Before (Mixed Types)

```php
$port = env('APP_PORT');        // string("8080") or int(8080)?
$debug = config('app.debug');   // mixed (could be anything)

// Need manual validation
$port = is_numeric($port) ? (int)$port : 8080;
```

### After (Type-Safe)

```php
$port = TypedEnv::getIntOr('APP_PORT', 8080);  // int(8080), guaranteed
$debug = TypedConfig::getBool('app.debug');     // bool, or throws
```

## PHPStan Integration

The package works seamlessly with PHPStan at max level:

```php
/** @var int $port */
$port = TypedEnv::getInt('APP_PORT'); // PHPStan knows this is int

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
- Laravel Version: 11+

## Comparison with Core Package

| Feature | `alexkart/typed-registry` | `alexkart/typed-registry-laravel` |
|---------|---------------------------|-----------------------------------|
| Framework | Framework-agnostic | Laravel-specific |
| Type Casting | None (strict only) | `EnvProvider` casts numeric strings |
| Facades | No | `TypedEnv`, `TypedConfig` |
| Auto-discovery | N/A | Yes |
| Dependencies | Zero | `illuminate/support`, `illuminate/contracts` |

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
