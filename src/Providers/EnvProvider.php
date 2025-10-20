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

        // Cast to int if it represents a whole number
        // This ensures "123" → 123, but not "123.0" or "1e3"
        if ((string) (int) $value === $value) {
            return (int) $value;
        }

        // Cast to float for decimals and scientific notation
        // This handles: "3.14", "1e3", "2.5e-4", etc.
        return (float) $value;
    }
}
