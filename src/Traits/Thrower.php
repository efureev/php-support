<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait Thrower
 * @package Php\Support\Traits
 */
trait Thrower
{
    /**
     * Throw Exception
     *
     * @param mixed ...$arguments
     */
    public static function throw(...$arguments): void
    {
        // @phpstan-ignore-next-line
        throw new static(...$arguments);
    }


    /**
     * @param mixed $value
     * @param mixed ...$arguments
     */
    public static function throwIf(mixed $value, ...$arguments): void
    {
        if ($value) {
            static::throw(...$arguments);
        }
    }

    /**
     * @param mixed $value
     * @param mixed ...$arguments
     *
     * @return bool
     */
    public static function throwIfReturn(mixed $value, ...$arguments): bool
    {
        static::throwIf($value, ...$arguments);

        return true;
    }
}
