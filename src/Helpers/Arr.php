<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use ArrayAccess;
use JsonSerializable;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Interfaces\Jsonable;
use Traversable;

/**
 * Class Arr
 *
 * @package Php\Support\Helpers
 */
class Arr
{
    /**
     * Remove one element from array by value
     *
     * @param array $array
     * @param mixed $val If $val is a string, the comparison is done in a case-sensitive manner.
     * @param bool $reindex
     *
     * @return string|int|null Index of removed element or null if don't exist
     */
    public static function removeByValue(array &$array, $val, $reindex = false)
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
     * @return array
     * @throws \Php\Support\Exceptions\JsonException
     */
    public static function toArray($items): array
    {
        if (is_array($items)) {
            $res = $items;
        } else if ($items instanceof Arrayable) {
            $res = $items->toArray();
        } else if ($items instanceof Jsonable) {
            $res = Json::decode($items->toJson());
        } else if ($items instanceof JsonSerializable) {
            $res = $items->jsonSerialize();
        } else if ($items instanceof Traversable) {
            $res = iterator_to_array($items);
        } else {
            $res = (array)$items;
        }


        return $res;
    }

    /**
     * Nested variable data to array
     *
     * @param mixed $items
     *
     * @return array|mixed|null
     * @throws \Php\Support\Exceptions\JsonException
     */
    public static function dataToArray($items)
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
            } else if ($items instanceof Traversable) {
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
     * Determine whether the given value is array accessible.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible($value): bool
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
    public static function exists($array, $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * @param array $res array to be merged to
     * @param array $b array to be merged from. You can specify additional
     *                            arrays via third argument, fourth argument etc.
     * @param bool $replaceArray Replace or Add values into Array, if key existed.
     *
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function merge($res, $b, $replaceArray = true): array
    {
        foreach ($b as $key => $val) {
            if (is_int($key)) {
                if (isset($res[$key])) {
                    $res[] = $val;
                } else {
                    $res[$key] = $val;
                }
            } else if (is_array($val) && isset($res[$key]) && is_array($res[$key])) {
                $res[$key] = ($replaceArray ? $val : self::merge($res[$key], $val, $replaceArray));
            } else {
                $res[$key] = $val;
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
     * @throws \Php\Support\Exceptions\JsonException
     */
    public static function toPostgresArray(array $array): string
    {
        if (!$json = Json::encode(self::toIndexedArray($array), JSON_UNESCAPED_UNICODE)) {
            return '{}';
        }

        return is_string($str = str_replace(['[', ']', '"'], ['{', '}', ''], $json)) ? $str : '{}';
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
}
