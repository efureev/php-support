<?php

namespace Php\Support\Helpers;

use Php\Support\Interfaces\Arrayable;
use Php\Support\Interfaces\Jsonable;

/**
 * Class Arr
 *
 * @package Php\Support\Helpers
 */
class Arr
{
    /**
     * Replace templates into array
     * Key = search value
     * Value = replace value
     *
     * @param array $array
     * @param array $replace
     */
    public static function arrayReplaceByTemplate(array &$array, array $replace)
    {
        foreach ($array as &$item) {
            static::itemReplaceByTemplate($item, $replace);
        }
    }

    /**
     * Replace templates into item
     *
     * @param mixed $item
     * @param array $replace
     */
    private static function itemReplaceByTemplate(&$item, array $replace)
    {
        if (is_array($item)) {
            self::arrayReplaceByTemplate($item, $replace);
        } else if (is_string($item)) {
            $item = Str::stringReplaceByTemplate($item, $replace);
        }
    }

    /**
     * Remove elements from array by value
     *
     * @param array $array
     * @param mixed $val
     */
    public static function removeByValue(array &$array, $val)
    {
        if (($key = array_search($val, $array)) !== false) {
            unset($array[$key]);
        }
    }

    /**
     * @param mixed $items
     *
     * @return array
     */
    public static function toArray($items): array
    {
        if (is_array($items)) {
            $res = $items;
        } elseif ($items instanceof Arrayable) {
            $res = $items->toArray();
        } elseif ($items instanceof Jsonable) {
            $res = Json::decode($items->toJson());
        } elseif ($items instanceof \JsonSerializable) {
            $res = $items->jsonSerialize();
        } elseif ($items instanceof \Traversable) {
            $res = iterator_to_array($items);
        } else {
            $res = (array)$items;
        }

        return $res;
    }

    /**
     * Apply class or type to every element into collection
     *
     * @param array $array
     * @param string $cls
     * @param \Closure|null $fn
     *
     * @return array
     */
    public static function applyCls(array $array, string $cls, \Closure $fn = null)
    {
        $fn = self::getNoopClosureForApplyCls($fn);

        return array_map(function ($element) use ($cls, $fn) {
            switch ($cls) {
                case 'array':
                    $result = (array)$element;
                    break;
                case 'string':
                    $result = \strval($element);
                    break;
                case 'integer':
                    $result = \intval($element);
                    break;

                default:
                    $result = $fn($cls, $element);
            }

            return $result;

        }, $array);
    }

    /**
     * @param \Closure|null $fn
     *
     * @return \Closure
     */
    public static function getNoopClosureForApplyCls(\Closure $fn = null)
    {
        if (is_null($fn)) {
            $fn = function ($cls, $data) {
                if (class_exists($cls)) {
                    return new $cls($data);
                }

                return null;
            };
        }

        return $fn;
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array $array
     * @param  null|string $key
     * @param  mixed $default
     *
     * @return mixed
     */
    public static function get($array, ?string $key, $default = null)
    {
        if (!static::accessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
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
     * Determine if the given key exists in the provided array.
     *
     * @param  \ArrayAccess|array $array
     * @param  string|int $key
     *
     * @return bool
     */
    public static function exists($array, $key)
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed $value
     *
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * @param array $res array to be merged to
     * @param array $b array to be merged from. You can specify additional
     *                            arrays via third argument, fourth argument etc.
     * @param bool $replaceArray Replace or Add values into Array, if key existed.
     *
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function merge($res, $b, $replaceArray = true)
    {
        foreach ($b as $key => $val) {
            if (is_int($key)) {
                if (isset($res[$key])) {
                    $res[] = $val;
                } else {
                    $res[$key] = $val;
                }
            } elseif (is_array($val) && isset($res[$key]) && is_array($res[$key])) {
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
     */
    public static function toPostgresArray(array $array): string
    {
        $json = \json_encode(self::toIndexedArray($array), JSON_UNESCAPED_UNICODE);

        return str_replace(['[', ']', '"'], ['{', '}', ''], $json);
    }

    /**
     * @param string|null $s
     * @param int $start
     * @param null $end
     *
     * @return array
     */
    public static function fromPostgresArray(?string $s, $start = 0, &$end = null): array
    {
        if (empty($s) || $s[0] !== '{') {
            return [];
        }

        $return = [];
        $string = false;
        $quote = '';
        $len = strlen($s);
        $v = '';

        for ($i = $start + 1; $i < $len; $i++) {
            $ch = $s[$i];
            if (!$string && $ch === '}') {
                if ($v !== '' || !empty($return)) {
                    $return[] = $v;
                }
                $end = $i;
                break;
            } else {
                if (!$string && $ch === '{') {
                    $v = self::fromPostgresArray($s, $i, $i);
                } else
                    if (!$string && $ch === ',') {
                        $return[] = $v;
                        $v = '';
                    } else
                        if (!$string && ($ch === '"' || $ch === "'")) {
                            $string = true;
                            $quote = $ch;
                        } else
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

        foreach ($return as &$r) {
            if (is_numeric($r)) {
                if (ctype_digit($r)) {
                    $r = (int)$r;
                } else {
                    $r = (float)$r;
                }
            }
        }

        return $return;
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
