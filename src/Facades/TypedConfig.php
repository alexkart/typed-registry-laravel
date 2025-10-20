<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * TypedConfig facade for type-safe Laravel configuration access.
 *
 * Provides static access to TypedRegistry with ConfigProvider (strict, no type casting).
 * Supports dot-notation for nested configuration values.
 *
 * @example
 * ```php
 * use TypedRegistry\Laravel\Facades\TypedConfig;
 *
 * $default = TypedConfig::getString('database.default');
 * $port = TypedConfig::getInt('database.connections.mysql.port');
 * $debug = TypedConfig::getBool('app.debug');
 * $hosts = TypedConfig::getStringList('app.allowed_hosts');
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
 * @see \TypedRegistry\Laravel\Providers\ConfigProvider
 */
final class TypedConfig extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @throws void
     */
    protected static function getFacadeAccessor(): string
    {
        return 'typed-registry.config';
    }
}
