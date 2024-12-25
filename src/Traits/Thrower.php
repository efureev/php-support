<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait Thrower
 */
trait Thrower
{
    public static function throw(mixed ...$arguments): void
    {
        // @phpstan-ignore-next-line
        throw new static(...$arguments);
    }

    public static function throwIf(mixed $value, mixed ...$arguments): void
    {
        if ($value) {
            static::throw(...$arguments);
        }
    }

    public static function throwIfReturn(mixed $value, mixed ...$arguments): bool
    {
        static::throwIf($value, ...$arguments);

        return true;
    }
}
