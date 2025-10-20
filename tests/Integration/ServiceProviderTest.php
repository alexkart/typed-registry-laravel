<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Tests\Integration;

use Orchestra\Testbench\TestCase;
use TypedRegistry\Laravel\Facades\TypedConfig;
use TypedRegistry\Laravel\Facades\TypedEnv;
use TypedRegistry\Laravel\TypedRegistryServiceProvider;
use TypedRegistry\TypedRegistry;

use function putenv;

/**
 * Integration tests for TypedRegistryServiceProvider and facades.
 */
final class ServiceProviderTest extends TestCase
{
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

    protected function tearDown(): void
    {
        putenv('TEST_VAR');
        parent::tearDown();
    }

    public function testEnvRegistryIsRegisteredInContainer(): void
    {
        $registry = $this->app->make('typed-registry.env');

        self::assertInstanceOf(TypedRegistry::class, $registry);
    }

    public function testConfigRegistryIsRegisteredInContainer(): void
    {
        $registry = $this->app->make('typed-registry.config');

        self::assertInstanceOf(TypedRegistry::class, $registry);
    }

    public function testEnvAndConfigRegistriesAreSeparateInstances(): void
    {
        $envRegistry = $this->app->make('typed-registry.env');
        $configRegistry = $this->app->make('typed-registry.config');

        self::assertNotSame($envRegistry, $configRegistry);
    }

    public function testTypedEnvFacadeWorks(): void
    {
        putenv('TEST_VAR=8080');

        $value = TypedEnv::getInt('TEST_VAR');

        self::assertSame(8080, $value);
    }

    public function testTypedConfigFacadeWorks(): void
    {
        $this->app['config']->set('app.name', 'TestApp');

        $value = TypedConfig::getString('app.name');

        self::assertSame('TestApp', $value);
    }

    public function testTypedEnvFacadePerformsTypeCasting(): void
    {
        putenv('TEST_VAR=123');

        // EnvProvider should cast "123" to int(123)
        $value = TypedEnv::getInt('TEST_VAR');

        self::assertSame(123, $value);
    }

    public function testTypedConfigFacadeDoesNotPerformTypeCasting(): void
    {
        $this->app['config']->set('test.port', '8080');

        // ConfigProvider should NOT cast, so this will throw
        $this->expectException(\TypedRegistry\RegistryTypeError::class);
        $this->expectExceptionMessage("key 'test.port' must be int, got '8080'");

        TypedConfig::getInt('test.port');
    }

    public function testTypedEnvFacadeSupportsDefaults(): void
    {
        $value = TypedEnv::getIntOr('NONEXISTENT_KEY', 9000);

        self::assertSame(9000, $value);
    }

    public function testTypedConfigFacadeSupportsDefaults(): void
    {
        $value = TypedConfig::getStringOr('nonexistent.key', 'default');

        self::assertSame('default', $value);
    }

    public function testTypedEnvFacadeSupportsLists(): void
    {
        putenv('TEST_VAR=not-a-list');

        $this->expectException(\TypedRegistry\RegistryTypeError::class);

        TypedEnv::getStringList('TEST_VAR');
    }

    public function testTypedConfigFacadeSupportsNestedKeys(): void
    {
        $this->app['config']->set('database.connections.mysql.port', 3306);

        $port = TypedConfig::getInt('database.connections.mysql.port');

        self::assertSame(3306, $port);
    }

    public function testServiceProviderIsDeferrable(): void
    {
        $provider = new TypedRegistryServiceProvider($this->app);

        $provides = $provider->provides();

        self::assertContains('typed-registry.env', $provides);
        self::assertContains('typed-registry.config', $provides);
    }

    public function testEnvRegistryIsSingleton(): void
    {
        $instance1 = $this->app->make('typed-registry.env');
        $instance2 = $this->app->make('typed-registry.env');

        self::assertSame($instance1, $instance2);
    }

    public function testConfigRegistryIsSingleton(): void
    {
        $instance1 = $this->app->make('typed-registry.config');
        $instance2 = $this->app->make('typed-registry.config');

        self::assertSame($instance1, $instance2);
    }
}
