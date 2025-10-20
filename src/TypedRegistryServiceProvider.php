<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use TypedRegistry\Laravel\Providers\ConfigProvider;
use TypedRegistry\Laravel\Providers\EnvProvider;
use TypedRegistry\TypedRegistry;

/**
 * Laravel service provider for TypedRegistry.
 *
 * Registers two TypedRegistry instances in the service container:
 * - Environment variables (with type casting)
 * - Configuration repository (strict, no casting)
 *
 * Supports Laravel's package auto-discovery feature.
 *
 * **Registered Bindings:**
 * - `'typed-registry.env'` → TypedRegistry with EnvProvider (for TypedEnv facade)
 * - `'typed-registry.config'` → TypedRegistry with ConfigProvider (for TypedConfig facade)
 */
final class TypedRegistryServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services in the container.
     *
     * @return void
     */
    public function register(): void
    {
        // Register TypedRegistry for environment variables (with type casting)
        $this->app->singleton('typed-registry.env', function (): TypedRegistry {
            return new TypedRegistry(new EnvProvider());
        });

        // Register TypedRegistry for config repository (strict, no casting)
        $this->app->singleton('typed-registry.config', function (Application $app): TypedRegistry {
            /** @var Repository $config */
            $config = $app->make('config');
            return new TypedRegistry(new ConfigProvider($config));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            'typed-registry.env',
            'typed-registry.config',
        ];
    }
}
