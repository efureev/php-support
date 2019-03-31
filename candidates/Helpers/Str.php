<?php

declare(strict_types=1);

namespace Php\Support\Candidates\Helpers;

use function mb_strtolower;
use function preg_replace;

/**
 * Class Str
 *
 * @package Php\Support\Helpers
 */
class Str
{
    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];
    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * Replace templates into string
     * Key = search value
     * Value = replace value
     *
     * @param string $str
     * @param array $replace
     *
     * @return mixed
     */
    public static function stringReplaceByTemplate(string $str, array $replace)
    {
        return str_replace(array_keys($replace), array_values($replace), $str);
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function studly($value): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Convert a string to snake case.
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake($value, $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, (string)$value);
            $value = mb_strtolower((string)$value, 'UTF-8');
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }
}
