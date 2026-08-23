<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

/**
 * Class Number
 * @package Php\Support\Helpers
 */
class Number
{
    /**
     * Number.MAX_SAFE_INTEGER from JavaScript (2^53 - 1).
     */
    public const int MAX_SAFE_INTEGER = 9007199254740991;

    /**
     * Convert an integer whose magnitude exceeds Number.MAX_SAFE_INTEGER (JavaScript) to string,
     * keeping safe integers as native int.
     *
     * Anything that is not an integer literal is returned unchanged as a string - including
     * fractional and exponent notation, which is never truncated.
     *
     * @param int|string $value
     *
     * @return int|string
     */
    public static function safeInt(int|string $value): int|string
    {
        if (is_int($value)) {
            return self::isSafeMagnitude((string)abs($value)) ? $value : (string)$value;
        }

        // Integer-like numeric string (no exponent / fractional part).
        if (preg_match('/^[+-]?\d+$/', $value)) {
            $digits = ltrim(ltrim($value, '+-'), '0');

            return self::isSafeMagnitude($digits === '' ? '0' : $digits)
                ? (int)$value
                : (string)$value;
        }

        // Non-integer values stay strings: casting '1.9' to int silently dropped the fraction,
        // which is the opposite of what a "safe integer" helper should do.
        return $value;
    }

    /**
     * Compares an absolute integer digit string against MAX_SAFE_INTEGER without precision loss.
     */
    private static function isSafeMagnitude(string $digits): bool
    {
        $threshold = (string)self::MAX_SAFE_INTEGER;

        if (strlen($digits) !== strlen($threshold)) {
            return strlen($digits) < strlen($threshold);
        }

        return strcmp($digits, $threshold) <= 0;
    }

    public static function isInteger(mixed $value): bool
    {
        if (is_int($value)) {
            return true;
        }

        return (is_string($value) || is_float($value)) && (string)$value === (string)(int)$value;
    }
}
