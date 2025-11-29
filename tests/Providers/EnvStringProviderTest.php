<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Tests\Providers;

use Orchestra\Testbench\TestCase;
use TypedRegistry\Laravel\Providers\EnvStringProvider;
use TypedRegistry\TypedRegistry;

use function putenv;

/**
 * Tests for EnvStringProvider type casting behavior.
 */
final class EnvStringProviderTest extends TestCase
{
    private EnvStringProvider $provider;
    private TypedRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new EnvStringProvider();
        $this->registry = new TypedRegistry($this->provider);
    }

    protected function tearDown(): void
    {
        // Clean up environment variables
        putenv('TEST_VAR');
        parent::tearDown();
    }

    public function testGetKeepsIntegerStringsAsStrings(): void
    {
        putenv('TEST_VAR=123');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('123', $value);
        self::assertIsString($value);
    }

    public function testGetKeepsNegativeIntegerStringsAsStrings(): void
    {
        putenv('TEST_VAR=-456');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('-456', $value);
        self::assertIsString($value);
    }

    public function testGetKeepsZeroStringAsString(): void
    {
        putenv('TEST_VAR=0');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('0', $value);
        self::assertIsString($value);
    }

    public function testGetKeepsFloatStringsAsStrings(): void
    {
        putenv('TEST_VAR=3.14');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('3.14', $value);
        self::assertIsString($value);
    }

    public function testGetKeepsScientificNotationAsString(): void
    {
        putenv('TEST_VAR=1e3');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('1e3', $value);
        self::assertIsString($value);
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

    public function testGetCastsBooleanTrueToString(): void
    {
        putenv('TEST_VAR=true');
        $value = $this->provider->get('TEST_VAR');

        // Laravel's Env::get converts "true" to bool, then we cast to string
        self::assertSame('1', $value);
        self::assertIsString($value);
    }

    public function testGetCastsBooleanFalseToString(): void
    {
        putenv('TEST_VAR=false');
        $value = $this->provider->get('TEST_VAR');

        // Laravel's Env::get converts "false" to bool, then we cast to string
        self::assertSame('', $value);
        self::assertIsString($value);
    }

    public function testGetKeepsNullAsNull(): void
    {
        putenv('TEST_VAR=null');
        $value = $this->provider->get('TEST_VAR');

        // Laravel's Env::get converts "null" to null, which is not scalar
        self::assertNull($value);
    }

    public function testGetReturnsNullForMissingKey(): void
    {
        $value = $this->provider->get('NONEXISTENT_KEY');

        self::assertNull($value);
    }

    public function testIntegrationNumericPasswordWithTypedRegistry(): void
    {
        // Main use case: password that happens to be all digits
        putenv('TEST_VAR=123456');
        $password = $this->registry->getStringOr('TEST_VAR', '');

        self::assertSame('123456', $password);
    }

    public function testIntegrationGetStringWorksWithNumericValues(): void
    {
        putenv('TEST_VAR=8080');
        $value = $this->registry->getString('TEST_VAR');

        self::assertSame('8080', $value);
    }

    public function testIntegrationGetStringOrWorksWithNumericValues(): void
    {
        putenv('TEST_VAR=42');
        $value = $this->registry->getStringOr('TEST_VAR', 'default');

        self::assertSame('42', $value);
    }

    public function testIntegrationGetNullableStringWorksWithNumericValues(): void
    {
        putenv('TEST_VAR=999');
        $value = $this->registry->getNullableString('TEST_VAR');

        self::assertSame('999', $value);
    }

    public function testIntegrationGetNullableStringReturnsNullForNullEnv(): void
    {
        putenv('TEST_VAR=null');
        $value = $this->registry->getNullableString('TEST_VAR');

        self::assertNull($value);
    }

    public function testGetKeepsLeadingZerosAsStrings(): void
    {
        putenv('TEST_VAR=042');
        $value = $this->provider->get('TEST_VAR');

        // Leading zeros preserved as string
        self::assertSame('042', $value);
        self::assertIsString($value);
    }

    public function testDoesNotCastMixedAlphanumericStrings(): void
    {
        putenv('TEST_VAR=abc123');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('abc123', $value);
        self::assertIsString($value);
    }

    public function testGetKeepsLargeNumbersAsStrings(): void
    {
        putenv('TEST_VAR=9223372036854775808');
        $value = $this->provider->get('TEST_VAR');

        self::assertSame('9223372036854775808', $value);
        self::assertIsString($value);
    }
}
