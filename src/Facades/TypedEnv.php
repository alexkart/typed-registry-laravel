<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * TypedEnv facade for type-safe Laravel environment variable access.
 *
 * Provides static access to TypedRegistry with EnvProvider (includes type casting
 * for numeric strings).
 *
 * @example
 * ```php
 * use TypedRegistry\Laravel\Facades\TypedEnv;
 *
 * $debug = TypedEnv::getBool('APP_DEBUG');       // "true" → true
 * $port = TypedEnv::getInt('APP_PORT');          // "8080" → 8080
 * $timeout = TypedEnv::getFloat('APP_TIMEOUT');  // "2.5" → 2.5
 * $name = TypedEnv::getString('APP_NAME');       // "Laravel" → "Laravel"
 * ```
 *
 * @method static string getString(string $key)
 * @method static int getInt(string $key)
 * @method static bool getBool(string $key)
 * @method static float getFloat(string $key)
 * @method static string|null getNullableString(string $key)
 * @method static int|null getNullableInt(string $key)
 * @method static bool|null getNullableBool(string $key)
 * @method static float|null getNullableFloat(string $key)
 * @method static string getStringOr(string $key, string $default)
 * @method static int getIntOr(string $key, int $default)
 * @method static bool getBoolOr(string $key, bool $default)
 * @method static float getFloatOr(string $key, float $default)
 * @method static list<string> getStringList(string $key)
 * @method static list<int> getIntList(string $key)
 * @method static list<bool> getBoolList(string $key)
 * @method static list<float> getFloatList(string $key)
 * @method static array<string, string> getStringMap(string $key)
 * @method static array<string, int> getIntMap(string $key)
 * @method static array<string, bool> getBoolMap(string $key)
 * @method static array<string, float> getFloatMap(string $key)
 *
 * @see \TypedRegistry\TypedRegistry
 * @see \TypedRegistry\Laravel\Providers\EnvProvider
 */
final class TypedEnv extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @throws void
     */
    protected static function getFacadeAccessor(): string
    {
        return 'typed-registry.env';
    }
}
