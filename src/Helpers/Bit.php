<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

/**
 * Class Bit
 *
 * @package Php\Support\Helpers
 *
 *
 * Working with bits and bitmasks.
 */
class Bit
{
    /**
     * Remove bit from $value
     *
     * @param int|string $value
     * @param int $bit
     *
     * @return int
     */
    public static function removeFlag($value, int $bit): int
    {
        return static::toInt($value) & ~$bit;
    }

    /**
     * Set bit to $value
     *
     * @param int|string $value
     * @param int $bit
     *
     * @return int
     */
    public static function addFlag($value, int $bit): int
    {
        return static::toInt($value) | $bit;
    }

    /**
     * @param string|int $value
     *
     * @return int
     */
    protected static function toInt($value): int
    {
        if (is_string($value)) {
            return (int)bindec($value);
        }

        return (int)$value;
    }

    /**
     * Check bit into $value
     *
     * @param int|string $value
     * @param int $bit
     *
     * @return bool
     */
    public static function checkFlag($value, int $bit): bool
    {
        return (static::toInt($value) & $bit) > 0;
    }


    /**
     * Check a bit is existing in flag`s list
     *
     * @param array $list
     * @param int $bit
     *
     * @return bool
     */
    public static function exist(array $list, int $bit): bool
    {
        return self::checkFlag(self::grant($list), $bit);
    }

    /**
     * Return value of sum of all bits in list
     *
     * @param array $list
     *
     * @return int
     */
    public static function grant(array $list): int
    {
        return array_reduce($list, fn($prev, $next) => $prev | $next, 0);
    }

    /**
     * Convert decimal to binary string with left pad zero-filling
     *
     * @param int $bit
     * @param int $length
     *
     * @return string
     */
    public static function decBinPad(int $bit, int $length): string
    {
        return sprintf("%0{$length}d", decbin($bit));
    }
}
