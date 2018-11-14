<?php

namespace Php\Support\Helpers;

/**
 * Class Str
 *
 * @package Php\Support\Helpers
 */
class Str
{
    /**
     * Replace templates into string
     * Key = search value
     * Value = replace value
     *
     * @param string $str
     * @param array  $replace
     *
     * @return mixed
     */
    public static function stringReplaceByTemplate(string $str, array $replace)
    {
        return str_replace(array_keys($replace), array_values($replace), $str);
    }

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Convert a value to studly caps case.
     *
     * @param  string $value
     *
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[ $key ])) {
            return static::$studlyCache[ $key ];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[ $key ] = str_replace(' ', '', $value);
    }

    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * Convert a string to snake case.
     *
     * @param  string $value
     * @param  string $delimiter
     *
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value;

        if (isset(static::$snakeCache[ $key ][ $delimiter ])) {
            return static::$snakeCache[ $key ][ $delimiter ];
        }

        if (!ctype_lower($value)) {
            $value = \preg_replace('/\s+/u', '', ucwords($value));

            $value = \preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, (string)$value);
            $value = \mb_strtolower((string)$value, 'UTF-8');
        }

        return static::$snakeCache[ $key ][ $delimiter ] = $value;
    }
}