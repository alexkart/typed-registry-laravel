<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Tests\Providers;

use Illuminate\Config\Repository;
use Orchestra\Testbench\TestCase;
use TypedRegistry\Laravel\Providers\ConfigProvider;
use TypedRegistry\TypedRegistry;

/**
 * Tests for ConfigProvider (strict, no type casting).
 */
final class ConfigProviderTest extends TestCase
{
    private Repository $config;
    private ConfigProvider $provider;
    private TypedRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test config repository
        $this->config = new Repository([
            'app' => [
                'name' => 'Laravel',
                'debug' => true,
                'port' => 8080,
                'timeout' => 2.5,
            ],
            'database' => [
                'default' => 'mysql',
                'connections' => [
                    'mysql' => [
                        'host' => 'localhost',
                        'port' => 3306,
                        'database' => 'test_db',
                    ],
                ],
            ],
            'features' => [
                'enabled' => ['auth', 'api', 'admin'],
            ],
            'labels' => [
                'env' => 'production',
                'tier' => 'web',
            ],
        ]);

        $this->provider = new ConfigProvider($this->config);
        $this->registry = new TypedRegistry($this->provider);
    }

    public function testGetReturnsStringValue(): void
    {
        $value = $this->provider->get('app.name');

        self::assertSame('Laravel', $value);
    }

    public function testGetReturnsBoolValue(): void
    {
        $value = $this->provider->get('app.debug');

        self::assertSame(true, $value);
    }

    public function testGetReturnsIntValue(): void
    {
        $value = $this->provider->get('app.port');

        self::assertSame(8080, $value);
    }

    public function testGetReturnsFloatValue(): void
    {
        $value = $this->provider->get('app.timeout');

        self::assertSame(2.5, $value);
    }

    public function testGetSupportsNestedDotNotation(): void
    {
        $value = $this->provider->get('database.connections.mysql.host');

        self::assertSame('localhost', $value);
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        $value = $this->provider->get('nonexistent.key');

        self::assertNull($value);
    }

    public function testGetReturnsArrayValue(): void
    {
        $value = $this->provider->get('features.enabled');

        self::assertSame(['auth', 'api', 'admin'], $value);
    }

    public function testIntegrationWithTypedRegistryString(): void
    {
        $name = $this->registry->getString('app.name');

        self::assertSame('Laravel', $name);
    }

    public function testIntegrationWithTypedRegistryInt(): void
    {
        $port = $this->registry->getInt('database.connections.mysql.port');

        self::assertSame(3306, $port);
    }

    public function testIntegrationWithTypedRegistryBool(): void
    {
        $debug = $this->registry->getBool('app.debug');

        self::assertTrue($debug);
    }

    public function testIntegrationWithTypedRegistryFloat(): void
    {
        $timeout = $this->registry->getFloat('app.timeout');

        self::assertSame(2.5, $timeout);
    }

    public function testIntegrationWithTypedRegistryStringList(): void
    {
        $features = $this->registry->getStringList('features.enabled');

        self::assertSame(['auth', 'api', 'admin'], $features);
    }

    public function testIntegrationWithTypedRegistryStringMap(): void
    {
        $labels = $this->registry->getStringMap('labels');

        self::assertSame(['env' => 'production', 'tier' => 'web'], $labels);
    }

    public function testDoesNotPerformTypeCoercion(): void
    {
        // Store a string that looks like a number
        $this->config->set('app.port_string', '8080');

        $value = $this->provider->get('app.port_string');

        // Should remain a string, not cast to int
        self::assertSame('8080', $value);
        self::assertIsString($value);
    }

    public function testReturnsWholeArrayForTopLevelKey(): void
    {
        $value = $this->provider->get('app');

        self::assertIsArray($value);
        self::assertArrayHasKey('name', $value);
        self::assertArrayHasKey('debug', $value);
        self::assertSame('Laravel', $value['name']);
    }
}
