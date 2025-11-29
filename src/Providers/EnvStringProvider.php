<?php

declare(strict_types=1);

namespace TypedRegistry\Laravel\Providers;

use Illuminate\Support\Env;
use TypedRegistry\Provider;

/**
 * Laravel environment variable provider that casts all scalar values to strings.
 *
 * This provider is useful when you need to retrieve environment variables that
 * are semantically strings but may contain only digits (e.g., passwords, tokens,
 * API keys). Unlike EnvProvider which casts numeric strings to int/float, this
 * provider ensures all scalar values are returned as strings.
 *
 * **Type Casting Behavior:**
 * - All scalar values (string, int, float, bool) → string
 * - Booleans: true → "1", false → "" (PHP's native casting)
 * - Null remains null
 *
 * **Use Case:**
 * When you have environment variables like passwords that might be all-numeric:
 * ```
 * # .env
 * API_PASSWORD=123456
 * ```
 *
 * Using EnvProvider, `getStringOr('API_PASSWORD', '')` would return '' because
 * the value is cast to int first. Using EnvStringProvider, it returns "123456".
 *
 * @example
 * ```php
 * // .env file:
 * // API_PASSWORD=123456
 * // API_TOKEN=abc123
 * // FEATURE_FLAG=true
 *
 * $env = new TypedRegistry(new EnvStringProvider());
 * $password = $env->getString('API_PASSWORD');  // "123456" (not int)
 * $token = $env->getString('API_TOKEN');        // "abc123"
 * $flag = $env->getString('FEATURE_FLAG');      // "1" (bool cast to string)
 * ```
 */
final class EnvStringProvider implements Provider
{
    /**
     * Retrieve an environment variable value, casting all scalars to strings.
     *
     * @param string $key The environment variable name
     * @return mixed The value as a string (for scalars), or null if not found
     */
    public function get(string $key): mixed
    {
        $value = Env::get($key);

        if (is_scalar($value)) {
            return (string) $value;
        }

        return $value;
    }
}
