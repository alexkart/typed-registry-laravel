<?php

declare(strict_types=1);

use TypedRegistry\Laravel\Providers\ConfigProvider;
use TypedRegistry\Laravel\Providers\EnvProvider;
use TypedRegistry\TypedRegistry;

if (! function_exists('typedEnv')) {
    /**
     * Get a typed environment variable registry with intelligent casting.
     *
     * This helper provides type-safe access to environment variables with automatic
     * type casting (numeric strings → int/float, booleans, nulls). Safe to use in
     * config files where facades are not available.
     *
     * @return TypedRegistry A typed registry for accessing environment variables
     *
     * @example
     * ```php
     * // In config files:
     * $port = typedEnv()->getInt('APP_PORT');              // "8080" → 8080
     * $timeout = typedEnv()->getFloat('TIMEOUT');          // "2.5" → 2.5
     * $debug = typedEnv()->getBool('APP_DEBUG');           // "true" → true
     * $name = typedEnv()->getStringOr('APP_NAME', 'Laravel');
     * ```
     */
    function typedEnv(): TypedRegistry
    {
        return new TypedRegistry(new EnvProvider);
    }
}

if (! function_exists('typedConfig')) {
    /**
     * Get a typed configuration registry (strict, no casting).
     *
     * This helper provides type-safe access to Laravel config values with strict
     * type validation (no automatic casting). Safe to use in service providers
     * where facades may not be available.
     *
     * @return TypedRegistry A typed registry for accessing configuration values
     *
     * @example
     * ```php
     * // In service providers or early boot:
     * $driver = typedConfig()->getString('database.default');
     * $port = typedConfig()->getInt('database.connections.mysql.port');
     * $options = typedConfig()->getStringMap('database.connections.mysql.options');
     * ```
     */
    function typedConfig(): TypedRegistry
    {
        return new TypedRegistry(new ConfigProvider);
    }
}
