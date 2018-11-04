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
     * Remove named keys from arrays
     *
     * @param array $array
     *
     * @return array
     */
    public static function removeKeys(array $array): array
    {
        $array = array_values($array);
        foreach ($array as &$value) {
            if (is_array($value)) $value = static::removeKeys($value);
        }

        return $array;
    }

    /**
     * Changes PHP array to PostgreSQL array format
     *
     * @param array $array
     *
     * @return string
     */
    public static function ToPostgresArray(array $array): string
    {
        $array = \json_encode(self::removeKeys($array), JSON_UNESCAPED_UNICODE);

        return str_replace('[', '{', str_replace(']', '}', str_replace('"', '', $array)));
    }
}