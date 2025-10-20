<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Providers;

use Illuminate\Support\Env;
use TypedRegistry\Provider;

/**
 * Laravel environment variable provider with intelligent type casting.
 *
 * This provider wraps Laravel's Illuminate\Support\Env class and automatically
 * casts numeric strings to integers and floats, similar to how Laravel's env()
 * helper works with other types.
 *
 * **Type Casting Behavior:**
 * - Numeric strings → int (e.g., "123" → 123)
 * - Float strings → float (e.g., "3.14" → 3.14, "1e3" → 1000.0)
 * - Booleans already handled by Env::get() (e.g., "true" → true, "false" → false)
 * - Null already handled by Env::get() (e.g., "null" → null, "(null)" → null)
 * - Non-numeric strings pass through unchanged
 *
 * **Important:** This provider performs type coercion, which differs from
 * typed-registry's core philosophy of strict validation. Use this when you
 * trust your environment variable format and want automatic type conversion.
 *
 * @example
 * ```php
 * // .env file:
 * // APP_DEBUG=true
 * // APP_PORT=8080
 * // APP_TIMEOUT=2.5
 * // APP_NAME=Laravel
 *
 * $env = new TypedRegistry(new EnvProvider());
 * $debug = $env->getBool('APP_DEBUG');      // bool(true)
 * $port = $env->getInt('APP_PORT');         // int(8080)
 * $timeout = $env->getFloat('APP_TIMEOUT'); // float(2.5)
 * $name = $env->getString('APP_NAME');      // "Laravel"
 * ```
 */
final class EnvProvider implements Provider
{
    /**
     * Retrieve an environment variable value with intelligent type casting.
     *
     * @param string $key The environment variable name
     * @return mixed The value with appropriate type casting, or null if not found
     */
    public function get(string $key): mixed
    {
        $value = Env::get($key);

        // If not a string, return as-is (booleans/nulls already handled by Env::get)
        if (!is_string($value)) {
            return $value;
        }

        // Only cast numeric strings
        if (!is_numeric($value)) {
            return $value;
        }

        // Trim whitespace (is_numeric allows it, but we need clean strings)
        $value = trim($value);

        // Check if it's a float representation (contains decimal point or scientific notation)
        if (strpbrk($value, '.eE') !== false) {
            return (float) $value;
        }

        // For whole numbers, check if they fit in PHP's int range
        // We need to handle leading zeros/plus, so normalize the value first
        // Extract sign separately to handle negative numbers with leading zeros (e.g., "-042")
        $sign = '';
        $digits = $value;

        if ($digits[0] === '-' || $digits[0] === '+') {
            $sign = $digits[0] === '-' ? '-' : '';
            $digits = substr($digits, 1);
        }

        // Remove leading zeros from the digits
        $digits = ltrim($digits, '0');
        if ($digits === '') {
            $digits = '0';  // All zeros
        }

        $normalized = $sign . $digits;

        // Use filter_var on normalized value to check int range
        $intValue = filter_var($normalized, FILTER_VALIDATE_INT);
        if ($intValue !== false) {
            return $intValue;
        }

        // Number overflowed int range, return as float to preserve precision
        return (float) $value;
    }
}
