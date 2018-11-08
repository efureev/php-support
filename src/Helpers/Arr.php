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
            if (is_array($item)) {
                self::arrayReplaceByTemplate($item, $replace);
            } else if (is_string($item)) {
                $item = Str::stringReplaceByTemplate($item, $replace);
            }
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
            unset($array[ $key ]);
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
     * @param array         $array
     * @param string        $cls
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
        if (!$fn) {
            $fn = function ($cls, $data) {
                if (class_exists($cls)) {
                    return new $cls($data);
                }
            };
        }

        return $fn;
    }

}