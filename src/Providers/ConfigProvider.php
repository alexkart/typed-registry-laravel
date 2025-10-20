<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Providers;

use Illuminate\Contracts\Config\Repository;
use TypedRegistry\Provider;

/**
 * Laravel configuration repository provider (strict, no type casting).
 *
 * This provider wraps Laravel's configuration repository and provides
 * strict type-safe access to config values. Unlike EnvProvider, this
 * performs NO type coercion - values are returned exactly as stored.
 *
 * Supports dot-notation for nested configuration access.
 *
 * @example
 * ```php
 * // config/database.php:
 * // return [
 * //     'default' => 'mysql',
 * //     'connections' => [
 * //         'mysql' => ['host' => 'localhost', 'port' => 3306],
 * //     ],
 * // ];
 *
 * $config = new TypedRegistry(new ConfigProvider());
 * $default = $config->getString('database.default');           // "mysql"
 * $port = $config->getInt('database.connections.mysql.port');  // 3306
 * $host = $config->getString('database.connections.mysql.host'); // "localhost"
 * ```
 */
final class ConfigProvider implements Provider
{
    /**
     * Create a new config provider instance.
     *
     * @param Repository $config Laravel's config repository instance
     */
    public function __construct(private Repository $config)
    {
    }

    /**
     * Retrieve a configuration value for the given key.
     *
     * Supports dot-notation for nested values (e.g., "database.default").
     *
     * @param string $key The configuration key (supports dot notation)
     * @return mixed The configuration value, or null if not found
     */
    public function get(string $key): mixed
    {
        return $this->config->get($key);
    }
}
