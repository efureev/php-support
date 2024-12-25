<?php

declare(strict_types=1);

namespace Php\Support\Enums;

/**
 * @method static string[] values();
 * @mixin \BackedEnum
 */
trait WithEnhancesForStrings
{
    use WithEnhances {
        casesToString as casesToStringBase;
    }

    public static function casesToString(string $delimiter = ', ', ?callable $decorator = null): string
    {
        if ($decorator === null) {
            $decorator = static fn(self $enumItem) => "{$enumItem->value}";
        }

        return self::casesToStringBase($decorator, $delimiter);
    }

    public static function casesToEscapeString(string $delimiter = ', '): string
    {
        return static::casesToString($delimiter, static fn(self $enumItem) => "'$enumItem->value'");
    }

    public static function hasValue(string $value): bool
    {
        return in_array($value, static::values(), true);
    }
}
