<?php

namespace Php\Support\Helpers;

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


}