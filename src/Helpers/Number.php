<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use Php\Support\Exceptions\InvalidParamException;

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

    /**
     * Format a number with a fixed number of decimals.
     */
    public static function format(
        int|float $value,
        int $decimals = 0,
        string $decimalSeparator = '.',
        string $thousandsSeparator = ' '
    ): string {
        return number_format((float)$value, max(0, $decimals), $decimalSeparator, $thousandsSeparator);
    }

    /**
     * Constrain a value to the inclusive range [$min, $max].
     *
     * @throws InvalidParamException when the range is inverted
     */
    public static function clamp(int|float $value, int|float $min, int|float $max): int|float
    {
        if ($min > $max) {
            throw new InvalidParamException("Range is inverted: min $min is greater than max $max", 'min');
        }

        return min(max($value, $min), $max);
    }

    /**
     * What percentage $value is of $total, or null when $total is zero.
     */
    public static function percentage(int|float $value, int|float $total, int $precision = 2): ?float
    {
        if ((float)$total === 0.0) {
            return null;
        }

        return round($value / $total * 100, $precision);
    }

    /**
     * A byte count as a human-readable size: 1536 becomes "1.5 KB".
     *
     * @throws InvalidParamException for a negative size
     */
    public static function humanize(int|float $bytes, int $precision = 1): string
    {
        if ($bytes < 0) {
            throw new InvalidParamException("Size must not be negative, $bytes given", 'bytes');
        }

        $units = [
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
            'PB',
        ];
        $power = $bytes > 0 ? (int)floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        $size      = $bytes / (1024 ** $power);
        $formatted = number_format($size, $power === 0 ? 0 : max(0, $precision), '.', '');

        // trim trailing zeros of the fraction only - rtrim on the whole string ate the
        // integer part too, turning 500 bytes into "5 B"
        if (str_contains($formatted, '.')) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $formatted . ' ' . $units[$power];
    }

    /**
     * An integer with its English ordinal suffix: 1 becomes "1st".
     */
    public static function ordinal(int $value): string
    {
        $abs = abs($value);

        if ($abs % 100 >= 11 && $abs % 100 <= 13) {
            return $value . 'th';
        }

        return $value . match ($abs % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
