<?php

declare(strict_types=1);

namespace Php\Support\Enums;

use Php\Support\Exceptions\InvalidParamException;

/**
 * Helpers for backed enums.
 *
 * `values()`, `hasValue()` and `toValueKeyArray()` read `$case->value`, so the using enum must be
 * backed; `names()`, `hasName()` and the `*Name()` lookups work for any enum.
 *
 * @template TValue of string|int
 * @mixin \BackedEnum
 */
trait WithEnhances
{
    /**
     * Joins the cases into a string.
     *
     * @param null|callable(static): string $decorator Defaults to the case value
     * @param string $delimiter
     */
    public static function casesToString(?callable $decorator = null, string $delimiter = ', '): string
    {
        $decorator ??= static fn(self $enumItem) => (string)$enumItem->value;

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

    public static function hasName(string $name): bool
    {
        return in_array($name, static::names(), true);
    }

    /**
     * Check if the enum has the provided value.
     *
     * Accepts both string- and int-backed enums; {@see WithEnhancesForStrings::hasValue()}
     * narrows it to strings.
     */
    public static function hasValue(string|int $value): bool
    {
        return in_array($value, static::values(), true);
    }

    /**
     * Resolve a case by its name, or null when there is none.
     *
     * The counterpart of `tryFrom()`, which only looks at values.
     *
     * @return static|null
     */
    public static function tryFromName(string $name): ?static
    {
        foreach (static::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Resolve a case by its name or throw.
     *
     * @return static
     * @throws InvalidParamException
     */
    public static function fromName(string $name): static
    {
        return static::tryFromName($name)
            ?? throw new InvalidParamException(
                sprintf('"%s" is not a valid name for enum %s', $name, static::class),
                'name'
            );
    }

    /**
     * Names of the cases, keyed by value: ready to feed a select box.
     *
     * @return array<TValue, string>
     */
    public static function labels(): array
    {
        return static::toValueKeyArray();
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
     * @return array<TValue, string>
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
