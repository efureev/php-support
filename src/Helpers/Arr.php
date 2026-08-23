<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use Closure;
use ArrayAccess;
use ArrayObject;
use JsonSerializable;
use Php\Support\Exceptions\InvalidArgumentException;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Interfaces\Jsonable;
use Php\Support\Structures\Collections\ReadableCollection;
use Traversable;

use function array_diff_assoc;
use function array_merge;
use function array_rand;
use function array_search;
use function array_shift;
use function array_unique;
use function array_unshift;
use function array_values;
use function explode;
use function func_num_args;
use function is_array;
use function is_int;
use function is_numeric;
use function is_object;
use function iterator_to_array;
use function mb_substr;
use function str_contains;
use function str_replace;

/**
 * @template TKey of array-key
 * @template T
 */
class Arr
{
    /**
     * Collapse an array of arrays into a single array.
     *
     * @param iterable<TKey,T> $array
     * @return array<T>
     */
    public static function collapse(iterable $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof ReadableCollection) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }

    /**
     * Remove one element from array by value
     *
     * @param array<T> $array
     * @param mixed $val If $val is a string, the comparison is done in a case-sensitive manner.
     * @param bool $reindex
     * @param bool $strict Compare types as well. Loose by default for backward compatibility,
     *                     which means removeByValue(['1'], 1) removes the string '1'.
     *
     * @return string|int|null Index of removed element or null if it don't exist
     */
    public static function removeByValue(
        array &$array,
        mixed $val,
        bool $reindex = false,
        bool $strict = false
    ): string|int|null {
        $key = array_search($val, $array, $strict);

        if ($key === false) {
            return null;
        }

        unset($array[$key]);

        if ($reindex) {
            $array = array_values($array);
        }

        return $key;
    }

    /**
     * Simple variable to array
     *
     * @param mixed $items
     *
     * @return T[]|array<TKey, T>
     */
    public static function toArray(mixed $items): array
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof Arrayable) {
            return $items->toArray();
        }

        if ($items instanceof Traversable) {
            return iterator_to_array($items);
        }
        if ($items instanceof Jsonable) {
            $res = Json::decode($items->toJson());
            return is_array($res) ? $res : [];
        }

        if ($items instanceof JsonSerializable) {
            return (array)$items->jsonSerialize();
        }

        return (array)$items;
    }

    /**
     * Nested variable data to array
     *
     * @param mixed $items
     *
     * @return array|mixed|null
     */
    public static function dataToArray(mixed $items): mixed
    {
        if (is_object($items)) {
            if ($items instanceof JsonSerializable) {
                return static::dataToArray($items->jsonSerialize());
            }

            if ($items instanceof Jsonable) {
                return Json::decode($items->toJson());
            }

            if ($items instanceof Arrayable) {
                $items = $items->toArray();
            } elseif ($items instanceof Traversable) {
                $items = iterator_to_array($items);
            } else {
                $result = [];
                if (is_iterable($items)) {
                    foreach ($items as $name => $value) {
                        $result[$name] = $value;
                    }
                }
                $items = $result;
            }
        }

        if (!is_array($items)) {
            return $items;
        }

        foreach ($items as $key => &$value) {
            if (is_array($value) || is_object($value)) {
                $value = static::dataToArray($value);
            }
        }

        return $items;
    }

    /**
     * @param array<TKey, T> $res array to be merged to
     * @param array<TKey, T> $b array to be merged from. You can specify additional
     *                            arrays via third argument, fourth argument etc.
     * @param bool $replaceArray Replace or Add values into Array, if key existed.
     *
     * @return array<TKey, T> the merged array (the original arrays are not changed.)
     */
    public static function merge(array $res, array $b, bool $replaceArray = true): array
    {
        foreach ($b as $key => $val) {
            if (is_int($key)) {
                if (array_key_exists($key, $res)) {
                    $res[] = $val;
                } else {
                    $res[$key] = $val;
                }
            } else {
                if (is_array($val) && isset($res[$key]) && is_array($res[$key])) {
                    $res[$key] = ($replaceArray ? $val : self::merge($res[$key], $val, $replaceArray));
                } else {
                    $res[$key] = $val;
                }
            }
        }

        return $res;
    }

    /**
     * Changes PHP array to default Postgres array format.
     *
     * String keys are dropped, see {@see self::toIndexedArray()}.
     * Elements are quoted only when the PostgreSQL array literal syntax requires it: empty strings,
     * the literal `NULL` and values containing `{`, `}`, `,`, `"`, a backslash or whitespace.
     *
     * @param array<TKey, T> $array
     *
     * @return string
     */
    public static function toPostgresArray(array $array): string
    {
        return self::toPostgresArrayLiteral(self::toIndexedArray($array));
    }

    /**
     * @param array<int, mixed> $array
     */
    private static function toPostgresArrayLiteral(array $array): string
    {
        $parts = [];

        foreach ($array as $value) {
            $parts[] = self::toPostgresArrayElement($value);
        }

        return '{' . implode(',', $parts) . '}';
    }

    private static function toPostgresArrayElement(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }

        if (is_array($value) || $value instanceof Arrayable || $value instanceof Traversable) {
            return self::toPostgresArrayLiteral(self::toIndexedArray(self::toArray($value)));
        }

        return self::quotePostgresValue((string)$value);
    }

    /**
     * Wraps a value into double quotes when the PostgreSQL array literal syntax requires it.
     */
    private static function quotePostgresValue(string $value): string
    {
        if ($value !== '' && strcasecmp($value, 'NULL') !== 0 && !preg_match('/[{},"\\\\\\s]/', $value)) {
            return $value;
        }

        return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
    }

    /**
     * @param int[] $array
     * @return ?string
     */
    public static function toPostgresPoint(array $array): ?string
    {
        if (count($array) !== 2) {
            return null;
        }

        [
            $x,
            $y,
        ] = $array;

        return '(' . $x . ',' . $y . ')';
    }

    /**
     * Remove named keys from arrays
     *
     * @param array<TKey, T> $array
     *
     * @return array<T>
     */
    public static function toIndexedArray(array $array): array
    {
        $array = array_values($array);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::toIndexedArray($value);
            }
        }

        return $array;
    }

    /**
     * Load from PG array to PHP array
     *
     * @param string|null $s
     * @param int $start
     * @param ?int $end
     * @param array{string,string} $braces
     *
     * @return float[]
     */
    public static function fromPostgresArrayWithBraces(
        ?string $s,
        int $start = 0,
        ?int &$end = null,
        array $braces = [
            '{',
            '}',
        ]
    ): array {
        [
            $braceOpen,
            $braceClose,
        ] = $braces;
        if (empty($s) || $s[0] !== $braceOpen) {
            return [];
        }

        $return   = [];
        $string   = false;
        $quote    = '';
        $hasValue = false;
        $len      = strlen($s);
        $v        = '';

        for ($i = $start + 1; $i < $len; $i++) {
            $ch = $s[$i];

            if ($string) {
                if ($ch === '\\' && $i + 1 < $len) {
                    $v .= $s[++$i];
                    continue;
                }

                if ($ch === $quote) {
                    $string = false;
                    continue;
                }

                $v .= $ch;
                continue;
            }

            if ($ch === $braceClose) {
                if ($hasValue || $return !== []) {
                    $return[] = $v;
                }
                $end = $i;
                break;
            }

            if ($ch === $braceOpen) {
                $v        = self::fromPostgresArray($s, $i, $i);
                $hasValue = true;
                continue;
            }

            if ($ch === ',') {
                $return[] = $v;
                $v        = '';
                $hasValue = false;
                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $string   = true;
                $quote    = $ch;
                $hasValue = true;
                continue;
            }

            $v       .= $ch;
            $hasValue = true;
        }

        foreach ($return as &$r) {
            if (is_numeric($r)) {
                if (ctype_digit((string)$r)) {
                    $r = (int)$r;
                } else {
                    $r = (float)$r;
                }
            }
        }

        return $return;
    }

    /**
     * @param string|null $s
     * @param int $start
     * @param ?int $end
     *
     * @return float[]
     */
    public static function fromPostgresArray(?string $s, int $start = 0, ?int &$end = null): array
    {
        return static::fromPostgresArrayWithBraces($s, $start, $end, ['{', '}']);
    }

    /**
     * Parses a PostgreSQL point literal `(x,y)`.
     *
     * Returns `null` for anything that is not a well-formed point.
     *
     * @param ?string $value
     *
     * @return ?array{float,float}
     */
    public static function fromPostgresPoint(?string $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = '[-+]?\\d*\\.?\\d+(?:[eE][-+]?\\d+)?';

        if (!preg_match('/^\\(\\s*(' . $number . ')\\s*,\\s*(' . $number . ')\\s*\\)$/', $value, $m)) {
            return null;
        }

        return [
            (float)$m[1],
            (float)$m[2],
        ];
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param mixed $array
     * @param string|int|null $key
     * @param mixed $default
     * @param non-empty-string $separator
     */
    public static function get(
        mixed $array,
        string|int|null $key,
        mixed $default = null,
        string $separator = '.'
    ): mixed {
        if (!static::accessible($array)) {
            return value($default);
        }

        if ($key === null) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (is_int($key) || !str_contains($key, $separator)) {
            return $array[$key] ?? value($default);
        }

        foreach (explode($separator, $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param ArrayAccess<TKey, T>|array<TKey, T> $array
     * @param string|int $key
     *
     * @return bool
     */
    public static function exists(ArrayAccess|array $array, string|int $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     *
     * @param ArrayAccess<TKey,T>|array<TKey,T> $array
     * @param string|string[] $keys
     * @param non-empty-string $separator
     * @return bool
     */
    public static function has(ArrayAccess|array $array, string|array $keys, string $separator = '.'): bool
    {
        $keys = (array)$keys;

        if (!$array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (static::exists($array, $key)) {
                continue;
            }

            foreach (explode($separator, $key) as $segment) {
                if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param array<TKey,T>|ArrayObject<TKey,T>|array<mixed> $array
     * @param-out array<TKey,T>|ArrayObject<TKey,T>|array<mixed> $array
     * @param string $key
     * @param mixed $value
     * @param non-empty-string $separator
     * @return T[]|array<TKey,T>|ArrayObject<TKey,T>
     */
    public static function set(
        array|ArrayObject &$array,
        string $key,
        mixed $value,
        string $separator = '.'
    ): array|ArrayObject {
        $original = &$array;
        $keys     = explode($separator, $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $original;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array<TKey,T>|ArrayObject<TKey,T> $array
     * @param string[]|string $keys
     * @param non-empty-string $separator
     *
     * @return void
     */
    public static function remove(array|ArrayObject &$array, array|string $keys, string $separator = '.'): void
    {
        $original = &$array;
        $keys     = (array)$keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode($separator, $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Replace templates into array
     * Key = search value
     * Value = replace value
     *
     * @param array<TKey, T> $array
     * @param array<string, string> $replace
     *
     * @return array<TKey, T>
     */
    public static function replaceByTemplate(array $array, array $replace): array
    {
        return array_map(static fn($item) => self::itemReplaceByTemplate($item, $replace), $array);
    }

    /**
     * Replace templates into item
     *
     * @param mixed $item
     * @param array<string, string> $replace
     *
     * @return string|string[]|mixed
     */
    private static function itemReplaceByTemplate(mixed $item, array $replace): mixed
    {
        if (is_array($item)) {
            return self::replaceByTemplate($item, $replace);
        }

        if (is_string($item)) {
            return Str::replaceByTemplate($item, $replace);
        }

        return $item;
    }

    /**
     * Find duplicates into an array
     *
     * @param array<TKey, T> $array
     *
     * @return array<TKey, T>
     */
    public static function duplicates(array $array): array
    {
        return array_unique(array_diff_assoc($array, array_unique($array)));
    }

    /**
     * Fill a keyed array by values from another array
     *
     * @param array<TKey> $keys
     * @param array<T> $values
     *
     * @return array<TKey, T>
     */
    public static function fillKeysByValues(array $keys, array $values): array
    {
        $result = [];
        $values = array_values($values);

        foreach (array_values($keys) as $index => $keyName) {
            $result[$keyName] = $values[$index] ?? null;
        }

        return $result;
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param array<TKey, T> $array
     * @param mixed $value
     * @param string|int|null $key
     * @return array<TKey, T>
     */
    public static function prepend(array $array, mixed $value, string|int|null $key = null): array
    {
        if (func_num_args() === 2) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Get one or a specified number of random values from an array.
     *
     * @param array<TKey, T> $array
     * @param int|null $number
     * @param bool $preserveKeys
     * @return T[]|array<TKey, T>
     *
     * @throws InvalidArgumentException
     */
    public static function random(array $array, ?int $number = null, bool $preserveKeys = false): mixed
    {
        $requested = $number ?? 1;

        $count = count($array);

        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if ($number === 0) {
            return [];
        }

        if ($number === null) {
            return $array[array_rand($array)];
        }

        $keys = array_rand($array, $number);

        $results = [];

        if ($preserveKeys) {
            foreach ((array)$keys as $key) {
                $results[$key] = $array[$key];
            }
        } else {
            foreach ((array)$keys as $key) {
                $results[] = $array[$key];
            }
        }

        return $results;
    }

    /**
     * @template U
     * @param array<TKey, T> $elements
     * @param Closure $func
     * @phpstan-param Closure(array<TKey, T>): U[] $func
     * @return array<TKey, U>
     */
    public static function map(array $elements, Closure $func): array
    {
        $keys = array_keys($elements);
        $map  = array_map($func, $elements, $keys);

        return array_combine($keys, $map);
    }

    /**
     * Get a subset of the items, keeping only the given keys.
     *
     * @param array<TKey, T> $array
     * @param string[]|int[]|string|int $keys
     *
     * @return array<TKey, T>
     */
    public static function only(array $array, array|string|int $keys): array
    {
        return array_intersect_key($array, array_flip((array)$keys));
    }

    /**
     * Get all of the items except for those with the given keys.
     *
     * @param array<TKey, T> $array
     * @param string[]|int[]|string|int $keys
     *
     * @return array<TKey, T>
     */
    public static function except(array $array, array|string|int $keys): array
    {
        return array_diff_key($array, array_flip((array)$keys));
    }

    /**
     * Pluck an array of values from an array, optionally keyed by another field.
     *
     * Both `$value` and `$key` support the "dot" notation understood by {@see self::get()}.
     *
     * @param iterable<TKey, T> $array
     * @param string|int|null $value
     * @param string|int|null $key
     *
     * @return array<mixed>
     */
    public static function pluck(iterable $array, string|int|null $value, string|int|null $key = null): array
    {
        $results = [];

        foreach ($array as $item) {
            $itemValue = $value === null ? $item : dataGet($item, $value);

            if ($key === null) {
                $results[] = $itemValue;
                continue;
            }

            $itemKey = dataGet($item, $key);
            $results[is_object($itemKey) && $itemKey instanceof \Stringable ? (string)$itemKey : $itemKey] = $itemValue;
        }

        return $results;
    }

    /**
     * Return the first element passing a given truth test.
     *
     * @param iterable<TKey, T> $array
     * @param null|callable(T, TKey): bool $callback
     * @param mixed $default
     *
     * @return mixed
     */
    public static function first(iterable $array, ?callable $callback = null, mixed $default = null): mixed
    {
        foreach ($array as $key => $value) {
            if ($callback === null || $callback($value, $key)) {
                return $value;
            }
        }

        return value($default);
    }

    /**
     * Return the last element passing a given truth test.
     *
     * @param array<TKey, T> $array
     * @param null|callable(T, TKey): bool $callback
     * @param mixed $default
     *
     * @return mixed
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            return $array === [] ? value($default) : end($array);
        }

        return static::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param iterable<mixed> $array
     * @param int $depth How many levels to flatten; INF for all of them.
     *
     * @return array<mixed>
     */
    public static function flatten(iterable $array, int|float $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
                continue;
            }

            if ($depth === 1) {
                $result = array_merge($result, array_values($item));
                continue;
            }

            $result = array_merge($result, static::flatten($item, $depth - 1));
        }

        return $result;
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param mixed $value
     *
     * @return array<mixed>
     */
    public static function wrap(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Flatten a nested array into a single level using the "dot" notation.
     *
     * @param array<mixed> $array
     * @param string $prepend
     * @param non-empty-string $separator
     *
     * @return array<string, mixed>
     */
    public static function dot(array $array, string $prepend = '', string $separator = '.'): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && $value !== []) {
                $results += static::dot($value, $prepend . $key . $separator, $separator);
                continue;
            }

            $results[$prepend . $key] = $value;
        }

        return $results;
    }

    /**
     * Expand a "dot" notated array back into a nested one. The inverse of {@see self::dot()}.
     *
     * @param array<string, mixed> $array
     * @param non-empty-string $separator
     *
     * @return array<mixed>
     */
    public static function undot(array $array, string $separator = '.'): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            static::set($results, (string)$key, $value, $separator);
        }

        return $results;
    }

    /**
     * Key an array by a field or using a callback.
     *
     * @param iterable<TKey, T> $array
     * @param (callable(T, TKey): array-key)|string|int $keyBy
     *
     * @return array<array-key, T>
     */
    public static function keyBy(iterable $array, callable|string|int $keyBy): array
    {
        $results = [];

        foreach ($array as $key => $item) {
            $resolved = is_callable($keyBy) ? $keyBy($item, $key) : dataGet($item, $keyBy);

            $results[$resolved instanceof \Stringable ? (string)$resolved : $resolved] = $item;
        }

        return $results;
    }

    /**
     * Filter the array using the given callback, preserving keys.
     *
     * @param array<TKey, T> $array
     * @param callable(T, TKey): bool $callback
     *
     * @return array<TKey, T>
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Determine whether the array is associative, that is: not a list.
     *
     * @param array<mixed> $array
     */
    public static function isAssoc(array $array): bool
    {
        return !array_is_list($array);
    }

    /**
     * Determine whether the array is a list: sequential integer keys starting at 0.
     *
     * @param array<mixed> $array
     */
    public static function isList(array $array): bool
    {
        return array_is_list($array);
    }
}
