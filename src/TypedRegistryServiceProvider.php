<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use TypedRegistry\Laravel\Providers\ConfigProvider;
use TypedRegistry\TypedRegistry;

/**
 * Laravel service provider for TypedRegistry.
 *
 * Registers TypedRegistry instance for configuration access in the service container.
 * Environment variables should be accessed via the typedEnv() helper function in
 * config files only, following Laravel best practices.
 *
 * Supports Laravel's package auto-discovery feature.
 *
 * **Registered Bindings:**
 * - `'typed-registry.config'` → TypedRegistry with ConfigProvider (for TypedConfig facade)
 *
 * **Helper Functions:**
 * - `typedEnv()` → Use in config files only for type-safe env access
 * - `typedConfig()` → Use anywhere for type-safe config access (same as TypedConfig facade)
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
        // Register TypedRegistry for config repository (strict, no casting)
        $this->app->singleton('typed-registry.config', function (): TypedRegistry {
            return new TypedRegistry(new ConfigProvider());
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
            'typed-registry.config',
        ];
    }
}
