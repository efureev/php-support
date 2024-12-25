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
     * Remove a bit from $value
     *
     * @param int|string $value
     * @param int $bit
     *
     * @return int
     */
    public static function removeFlag(int|string $value, int $bit): int
    {
        return static::toInt($value) & ~$bit;
    }

    /**
     * Set a bit to $value
     *
     * @param int|string $value
     * @param int $bit
     *
     * @return int
     */
    public static function addFlag(int|string $value, int $bit): int
    {
        return static::toInt($value) | $bit;
    }

    /**
     * @param string|int $value
     *
     * @return int
     */
    protected static function toInt(int|string $value): int
    {
        if (is_string($value)) {
            return (int)bindec($value);
        }

        return $value;
    }

    /**
     * Check bit into $value
     *
     * @param int|string $value
     * @param int $bit
     *
     * @return bool
     */
    public static function checkFlag(int|string $value, int $bit): bool
    {
        return (static::toInt($value) & $bit) > 0;
    }


    /**
     * Check a bit is existing in flag`s list
     *
     * @param int[] $list
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
     * @param int[] $list
     *
     * @return int
     */
    public static function grant(array $list): int
    {
        return array_reduce($list, fn(int $prev, int $next) => $prev | $next, 0);
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
