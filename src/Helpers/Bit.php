<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use Php\Support\Exceptions\InvalidParamException;

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
            if (!preg_match('/^[01]+$/', $value)) {
                throw new InvalidParamException("Value must be a binary string, '$value' given", 'value');
            }

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
        return (static::toInt($value) & $bit) !== 0;
    }


    /**
     * Check whether a bit is set in the combined mask of the given flags.
     *
     * @param int[] $list
     * @param int $bit
     *
     * @return bool
     */
    public static function hasFlagIn(array $list, int $bit): bool
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
     * Convert a non-negative integer to a binary string, left-padded with zeros.
     *
     * `$length` is a minimum width, not a fixed one: a value needing more bits is not truncated.
     *
     * @param int $bit Must not be negative
     * @param int $length Must not be negative
     *
     * @return string
     * @throws InvalidParamException for a negative value, whose two's complement representation
     *                               would be 64 characters wide and never what the caller meant
     */
    public static function decBinPad(int $bit, int $length): string
    {
        if ($bit < 0) {
            throw new InvalidParamException("Value must not be negative, $bit given", 'bit');
        }

        if ($length < 0) {
            throw new InvalidParamException("Length must not be negative, $length given", 'length');
        }

        return str_pad(decbin($bit), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Flip a bit: set it when clear, clear it when set.
     *
     * @param int|string $value
     * @param int $bit
     */
    public static function toggleFlag(int|string $value, int $bit): int
    {
        return static::toInt($value) ^ $bit;
    }

    /**
     * The individual bits that make up a mask, lowest first.
     *
     * @param int|string $value
     *
     * @return int[]
     */
    public static function flags(int|string $value): array
    {
        $mask   = static::toInt($value);
        $result = [];

        // Walk bit positions rather than doubling a probe: doubling past the highest bit
        // overflows to a negative value and then to 0, which loops forever for PHP_INT_MAX.
        for ($position = 0; $position < PHP_INT_SIZE * 8 - 1; $position++) {
            $bit = 1 << $position;

            if (($mask & $bit) !== 0) {
                $result[] = $bit;
            }
        }

        return $result;
    }

    /**
     * Whether every one of the given bits is set.
     *
     * @param int|string $value
     * @param int[] $bits
     */
    public static function hasAll(int|string $value, array $bits): bool
    {
        $mask = static::grant($bits);

        return $mask !== 0 && (static::toInt($value) & $mask) === $mask;
    }

    /**
     * Whether at least one of the given bits is set.
     *
     * @param int|string $value
     * @param int[] $bits
     */
    public static function hasAny(int|string $value, array $bits): bool
    {
        return (static::toInt($value) & static::grant($bits)) !== 0;
    }
}
