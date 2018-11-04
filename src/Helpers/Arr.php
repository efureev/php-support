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


}