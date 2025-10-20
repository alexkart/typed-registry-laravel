<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Tests\Providers;

use Orchestra\Testbench\TestCase;
use TypedRegistry\Laravel\Providers\EnvProvider;
use TypedRegistry\TypedRegistry;

use function putenv;

/**
 * Tests for EnvProvider type casting behavior.
 */
final class EnvProviderTest extends TestCase
{
    private EnvProvider $provider;
    private TypedRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new EnvProvider();
        $this->registry = new TypedRegistry($this->provider);
    }

    protected function tearDown(): void
    {
        // Clean up environment variables
        putenv('TEST_VAR');
        parent::tearDown();
    }

    public function testGetCastsIntegerStrings(): void
    {
        putenv('TEST_VAR=123');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame(123, $value);
        self::assertIsInt($value);
    }

    public function testGetCastsNegativeIntegerStrings(): void
    {
        putenv('TEST_VAR=-456');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame(-456, $value);
        self::assertIsInt($value);
    }

    public function testGetCastsZeroString(): void
    {
        putenv('TEST_VAR=0');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame(0, $value);
        self::assertIsInt($value);
    }

    public function testGetCastsFloatStrings(): void
    {
        putenv('TEST_VAR=3.14');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame(3.14, $value);
        self::assertIsFloat($value);
    }

    public function testGetCastsScientificNotation(): void
    {
        putenv('TEST_VAR=1e3');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame(1000.0, $value);
        self::assertIsFloat($value);
    }

    public function testGetCastsScientificNotationWithDecimal(): void
    {
        putenv('TEST_VAR=2.5e-4');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame(0.00025, $value);
        self::assertIsFloat($value);
    }

    public function testGetCastsZeroPointZeroAsFloat(): void
    {
        putenv('TEST_VAR=0.0');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame(0.0, $value);
        self::assertIsFloat($value);
    }

    public function testGetPreservesNonNumericStrings(): void
    {
        putenv('TEST_VAR=Laravel');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('Laravel', $value);
        self::assertIsString($value);
    }

    public function testGetPreservesEmptyStrings(): void
    {
        putenv('TEST_VAR=');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('', $value);
        self::assertIsString($value);
    }

    public function testGetHandlesBooleanTrue(): void
    {
        putenv('TEST_VAR=true');
        $value = $this->provider->get('TEST_VAR');

        // Laravel's Env::get handles this
        self::assertTrue($value);
        self::assertIsBool($value);
    }

    public function testGetHandlesBooleanFalse(): void
    {
        putenv('TEST_VAR=false');
        $value = $this->provider->get('TEST_VAR');

        // Laravel's Env::get handles this
        self::assertFalse($value);
        self::assertIsBool($value);
    }

    public function testGetHandlesNullString(): void
    {
        putenv('TEST_VAR=null');
        $value = $this->provider->get('TEST_VAR');

        // Laravel's Env::get handles this
        self::assertNull($value);
    }

    public function testGetHandlesParenthesizedNullString(): void
    {
        putenv('TEST_VAR=(null)');
        $value = $this->provider->get('TEST_VAR');

        // Laravel's Env::get handles this
        self::assertNull($value);
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        $value = $this->provider->get('NONEXISTENT_KEY');

        self::assertNull($value);
    }

    public function testIntegrationWithTypedRegistryInt(): void
    {
        putenv('TEST_VAR=8080');
        $port = $this->registry->getInt('TEST_VAR');

        self::assertSame(8080, $port);
    }

    public function testIntegrationWithTypedRegistryFloat(): void
    {
        putenv('TEST_VAR=2.5');
        $timeout = $this->registry->getFloat('TEST_VAR');

        self::assertSame(2.5, $timeout);
    }

    public function testIntegrationWithTypedRegistryString(): void
    {
        putenv('TEST_VAR=Laravel');
        $name = $this->registry->getString('TEST_VAR');

        self::assertSame('Laravel', $name);
    }

    public function testIntegrationWithTypedRegistryBool(): void
    {
        putenv('TEST_VAR=true');
        $debug = $this->registry->getBool('TEST_VAR');

        self::assertTrue($debug);
    }

    public function testDoesNotCastMixedAlphanumericStrings(): void
    {
        putenv('TEST_VAR=123abc');
        $value = $this->provider->get('TEST_VAR');

        // is_numeric() returns false for "123abc"
        self::assertSame('123abc', $value);
        self::assertIsString($value);
    }
}
