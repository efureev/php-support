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
     * @param int $value
     * @param int $bit
     *
     * @return int
     */
    public static function removeFlag(int $value, int $bit): int
    {
        $value &= ~$bit;

        return $value;
    }

    /**
     * Set bit to $value
     *
     * @param int $value
     * @param int $bit
     *
     * @return int
     */
    public static function addFlag(int $value, int $bit): int
    {
        $value |= $bit;

        return $value;
    }

    /**
     * Check bit into $value
     *
     * @param int $value
     * @param int $bit
     *
     * @return bool
     */
    public static function checkFlag(int $value, int $bit): bool
    {
        return ($value & $bit) > 0;
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
        return array_reduce(
            $list,
            static function ($prev, $next) {
                return $prev | $next;
            },
            0
        );
    }
}
