<?php

declare(strict_types=1);

namespace Php\Support\Enums;

/**
 * String-flavoured additions on top of {@see WithEnhances}.
 *
 * Everything else - values, names, hasValue, hasName, tryFromName, fromName, labels,
 * casesToString - comes from that trait unchanged. Until 6.0 this trait redeclared
 * casesToString() with the arguments the other way round, so the same call meant different
 * things depending on which trait an enum happened to use.
 *
 * @method static string[] values()
 * @mixin \BackedEnum
 */
trait WithEnhancesForStrings
{
    use WithEnhances;

    /**
     * Joins the case values into a string, each wrapped in single quotes.
     *
     * Handy for building an SQL IN list or an enum type definition.
     */
    public static function casesToEscapeString(string $delimiter = ', '): string
    {
        return static::casesToString(static fn(self $enumItem) => "'$enumItem->value'", $delimiter);
    }
}
