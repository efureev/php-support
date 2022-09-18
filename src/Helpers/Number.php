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
     * Convert large integer higher than Number.MAX_SAFE_INTEGER (JavaScript) to string.
     *
     * @param int|string $value
     *
     * @return int|string
     */
    public static function safeInt(int|string $value): int|string
    {
        if (is_int($value) && ($value >= 9007199254740991 || $value <= -9007199254740991)) {
            return (string)$value;
        }

        return is_numeric($value) ? (int)$value : (string)$value;
    }

    public static function isInteger(mixed $value): bool
    {
        /** @phpstan-ignore-next-line */
        return is_int($value) || (string)$value === (string)(int)($value);
    }
}
