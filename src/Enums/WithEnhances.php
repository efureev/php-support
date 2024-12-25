<?php

declare(strict_types=1);

namespace Php\Support\Enums;

/**
 * @template TValue
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
     * @return TValue[]
     */
    public static function values(): array
    {
        return array_map(static fn(self $enumItem) => $enumItem->value, self::cases());
    }

    /**
     * @return string[]
     */
    public static function names(): array
    {
        return array_map(static fn(self $enumItem) => $enumItem->name, self::cases());
    }

    public static function hasName(string $value): bool
    {
        return in_array($value, static::names(), true);
    }

    /**
     * @return array<string, TValue>
     */
    public static function toKeyValueArray(): array
    {
        $list = [];
        foreach (self::cases() as $case) {
            $list[$case->name] = $case->value;
        }

        return $list;
    }

    /**
     * @return array<string, string>
     */
    public static function toValueKeyArray(): array
    {
        $list = [];
        foreach (self::cases() as $case) {
            $list[$case->value] = $case->name;
        }

        return $list;
    }
}
