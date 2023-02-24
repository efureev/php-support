<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use ArrayAccess;
use ArrayObject;
use JsonSerializable;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Interfaces\Jsonable;
use Php\Support\Structures\Collections\ReadableCollection;
use Traversable;

/**
 * @psalm-template TKey of array-key
 * @psalm-template T
 */
class Arr
{
    /**
     * Collapse an array of arrays into a single array.
     *
     * @param iterable $array
     * @return array<int,mixed>
     * @psalm-return T[]
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
     * @param array $array
     * @param mixed $val If $val is a string, the comparison is done in a case-sensitive manner.
     * @param bool $reindex
     *
     * @return string|int|null Index of removed element or null if don't exist
     */
    public static function removeByValue(array &$array, mixed $val, bool $reindex = false): string|int|null
    {
        if (($key = array_search($val, $array, false)) !== false) {
            unset($array[$key]);
        }
        if ($reindex) {
            $array = array_values($array);
        }
        return $key ?: null;
    }

    /**
     * Simple variable to array
     *
     * @param mixed $items
     *
     * @return array<TKey, T>
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
     * @param array $res array to be merged to
     * @param array $b array to be merged from. You can specify additional
     *                            arrays via third argument, fourth argument etc.
     * @param bool $replaceArray Replace or Add values into Array, if key existed.
     *
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function merge(array $res, array $b, bool $replaceArray = true): array
    {
        foreach ($b as $key => $val) {
            if (is_int($key)) {
                if (isset($res[$key])) {
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
     * Changes PHP array to default Postgres array format
     *
     * @param array $array
     *
     * @return string
     */
    public static function toPostgresArray(array $array): string
    {
        if (!$json = Json::encode(self::toIndexedArray($array), JSON_UNESCAPED_UNICODE)) {
            return '{}';
        }

        return str_replace(['[', ']', '"'], ['{', '}', ''], $json);
    }

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
     * @param array $array
     *
     * @return array
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
     * @param array $braces
     *
     * @return array
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

        $return = [];
        $string = false;
        $quote  = '';
        $len    = strlen($s);
        $v      = '';

        for ($i = $start + 1; $i < $len; $i++) {
            $ch = $s[$i];
            if (!$string && $ch === $braceClose) {
                if ($v !== '' || !empty($return)) {
                    $return[] = $v;
                }
                $end = $i;
                break;
            } else {
                if (!$string && $ch === $braceOpen) {
                    $v = self::fromPostgresArray($s, (int)$i, $i);
                } else {
                    if (!$string && $ch === ',') {
                        $return[] = $v;
                        $v        = '';
                    } else {
                        if (!$string && ($ch === '"' || $ch === "'")) {
                            $string = true;
                            $quote  = $ch;
                        } else {
                            if ($string && $ch === $quote) {
                                if ($s[$i - 1] === "\\") {
                                    $v = substr($v, 0, -1) . $ch;
                                } else {
                                    $string = false;
                                }
                            } else {
                                $v .= $ch;
                            }
                        }
                    }
                }
            }
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
     * @return array
     */
    public static function fromPostgresArray(?string $s, int $start = 0, ?int &$end = null): array
    {
        return static::fromPostgresArrayWithBraces($s, $start, $end, ['{', '}']);
    }

    /**
     * @param string|null $value
     *
     * @return ?array
     */
    public static function fromPostgresPoint(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        $string = mb_substr($value, 1, -1);
        if (empty($string)) {
            return null;
        }

        [
            $x,
            $y,
        ] = explode(',', $string);
        return [
            (float)$x,
            (float)$y,
        ];
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param mixed $array
     * @param string|int|null $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get(mixed $array, string|int|null $key, mixed $default = null): mixed
    {
        if (!static::accessible($array)) {
            return value($default);
        }

        if ($key === null) {
            return $array;
        }

        /** @var array|ArrayAccess $array */
        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (is_int($key) || !str_contains($key, '.')) {
            return $array[$key] ?? value($default);
        }

        foreach (explode('.', $key) as $segment) {
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
     * @param ArrayAccess|array $array
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
     * @param ArrayAccess|array $array
     * @param string|array $keys
     *
     * @return bool
     */
    public static function has(ArrayAccess|array $array, string|array $keys): bool
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

            foreach (explode('.', $key) as $segment) {
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
     * @param array|ArrayObject|null $array
     * @param string $key
     * @param mixed $value
     *
     * @return array|ArrayObject|null
     */
    public static function set(array|ArrayObject|null &$array, string $key, mixed $value): array|ArrayObject|null
    {
        if ($array === null) {
            return $array;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param array|ArrayObject $array
     * @param array|string $keys
     *
     * @return void
     */
    public static function remove(array|ArrayObject &$array, array|string $keys): void
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

            $parts = explode('.', $key);

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
     * @param array $array
     * @param array $replace
     *
     * @return array
     */
    public static function replaceByTemplate(array $array, array $replace): array
    {
        $res = [];
        foreach ($array as $key => $item) {
            $res[$key] = self::itemReplaceByTemplate($item, $replace);
        }
        return $res;
    }

    /**
     * Replace templates into item
     *
     * @param mixed $item
     * @param array $replace
     *
     * @return array|mixed
     */
    private static function itemReplaceByTemplate(mixed $item, array $replace)
    {
        if (is_array($item)) {
            $item = self::replaceByTemplate($item, $replace);
        } else {
            if (is_string($item)) {
                $item = Str::replaceByTemplate($item, $replace);
            }
        }

        return $item;
    }

    /**
     * Find duplicates into an array
     *
     * @param array $array
     *
     * @return array
     */
    public static function duplicates(array $array): array
    {
        return array_unique(array_diff_assoc($array, array_unique($array)));
    }

    /**
     * Fill a keyed array by values from another array
     *
     * @param array $keys
     * @param array $values
     *
     * @return array
     */
    public static function fillKeysByValues(array $keys, array $values): array
    {
        $result = [];

        foreach ($keys as $key => $keyName) {
            $result[$keyName] = $values[$key] ?? null;
        }

        return $result;
    }

    /**
     * Push an item onto the beginning of an array.
     *
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     * @return array
     */
    public static function prepend(array $array, mixed $value, mixed $key = null): array
    {
        if (func_num_args() == 2) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }
}
