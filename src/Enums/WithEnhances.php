<?php

declare(strict_types=1);

namespace Php\Support\Enums;

/**
 * @mixin \UnitEnum
 */
trait WithEnhances
{
    public static function casesToString(callable $decorator, string $delimiter = ', '): string
    {
        return implode(
            $delimiter,
            array_map($decorator, self::cases())
        );
    }

    /**
     * @return mixed[]
     */
    public static function values(): array
    {
        return array_map(static fn(self $enumItem) => $enumItem->value, self::cases());
    }

    public static function names(): array
    {
        return array_map(static fn(self $enumItem) => $enumItem->name, self::cases());
    }

    public static function hasName(string $value): bool
    {
        return in_array($value, static::names(), true);
    }
}
