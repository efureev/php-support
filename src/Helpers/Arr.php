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
            return $items;
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return Json::decode($items->toJson());
        } elseif ($items instanceof \JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof \Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }


}