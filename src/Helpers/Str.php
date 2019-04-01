<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

/**
 * Class Str
 *
 * @package Php\Support\Helpers
 */
class Str
{
    /**
     * Remove all multi-spaced characters
     *
     * @param string $str
     *
     * @return string
     */
    public static function removeMultiSpace(string $str): string
    {
        $res = \preg_replace('/\s+/', ' ', $str);
        return is_string($res) ? $res : $str;
    }

    /**
     * Converts a string to snake_case
     *
     * @param string $str
     *
     * @return string
     */
    public static function toSnake(string $str): string
    {
        return self::toDelimited($str, '_');
    }

    /**
     * Converts a string to SCREAMING_SNAKE_CASE
     *
     * @param string $str
     *
     * @return string
     */
    public static function toScreamingSnake(string $str): string
    {
        return self::toScreamingDelimited($str, '_', true);
    }

    /**
     * Converts a string to kebab-case
     *
     * @param string $str
     *
     * @return string
     */
    public static function toKebab(string $str): string
    {
        return self::toDelimited($str, '-');
    }

    /**
     * Converts a string to delimited.snake.case (in this case `del = '.'`)
     *
     * @param string $str
     * @param string $delimiter
     *
     * @return string
     */
    public static function toDelimited(string $str, string $delimiter): string
    {
        return self::toScreamingDelimited($str, $delimiter, false);
    }


    /**
     * The cache of delimited converted-cased words.
     *
     * @var array
     */
    protected static $delimitedCache = [];

    /**
     * Converts a string to SCREAMING.DELIMITED.SNAKE.CASE (in this case `del = '.'; screaming = true`) or
     * delimited.snake.case (in this case `del = '.'; screaming = false`)
     *
     * @param string $str
     * @param string $delimiter
     * @param bool $screaming
     *
     * @return string
     */
    public static function toScreamingDelimited(string $str, string $delimiter, bool $screaming): string
    {
        $str = self::removeMultiSpace($str);
        $str = self::addWordBoundariesToNumbers($str);
        $str = \trim($str);

        if (isset(static::$delimitedCache[$str][$delimiter][$screaming])) {
            return static::$delimitedCache[$str][$delimiter][$screaming];
        }

        $res = '';
        $len = \mb_strlen($str, 'UTF-8');

        $get_letter = static function (int $idx, string $s) {
            return \mb_substr($s, $idx, 1, 'UTF-8');
        };

        for ($i = 0; $i < $len; $i++) {
            // treat acronyms as words, eg for JSONData -> JSON is a whole word
            $next_case_is_changed = false;
            $letter = $get_letter($i, $str);

            if ($i + 1 < $len) {
                $next_letter = $get_letter($i + 1, $str);
                if (
                    ($letter >= 'A' && $letter <= 'Z' && $next_letter >= 'a' && $next_letter <= 'z') ||
                    ($letter >= 'a' && $letter <= 'z' && $next_letter >= 'A' && $next_letter <= 'Z')
                ) {
                    $next_case_is_changed = true;
                }
            }

            if ($i > 0 && ($get_letter(\mb_strlen($res, 'UTF-8') - 1, $res) !== $delimiter) && $next_case_is_changed) {
                // add underscore if next letter case type is changed
                if ($letter >= 'A' && $letter <= 'Z') {
                    $res .= $delimiter . $letter;
                } else if ($letter >= 'a' && $letter <= 'z') {
                    $res .= $letter . $delimiter;
                }
            } else if ($letter === ' ' || $letter === '_' || $letter === '-') {
                // replace spaces/underscores with delimiters
                $res .= $delimiter;
            } else {
                $res .= $letter;
            }
        }

        if ($screaming) {
            $res = \mb_strtoupper($res, 'UTF-8');
        } else {
            $res = \mb_strtolower($res, 'UTF-8');
        }

        return static::$delimitedCache[$str][$delimiter][$screaming] = $res;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private static function addWordBoundariesToNumbers(string $str): string
    {
        $res = \preg_replace('/([a-zA-Z])(\d+)([a-zA-Z]?)/u', '$1 $2 $3', $str);
        return is_string($res) ? $res : $str;
    }

    /**
     * Converts a string to CamelCase
     *
     * @param string $str
     *
     * @return string
     */
    public static function toCamel(string $str): string
    {
        return self::toCamelInitCase($str, true);
    }

    /**
     * Converts a string to lowerCamelCase
     *
     * @param string $str
     *
     * @return string
     */
    public static function toLowerCamel(string $str): string
    {
        if ($str === '') {
            return $str;
        }

        return self::toCamelInitCase(lcfirst($str), false);
    }

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * Converts a string to CamelCase
     *
     * @param string $str
     * @param bool $initCase
     *
     * @return string
     */
    public static function toCamelInitCase(string $str, bool $initCase): string
    {
        $str = self::removeMultiSpace($str);
        $str = self::addWordBoundariesToNumbers($str);
        $str = \trim($str);

        if (isset(static::$delimitedCache[$str][$initCase])) {
            return static::$delimitedCache[$str][$initCase];
        }


        $len = \mb_strlen($str, 'UTF-8');

        $get_letter = static function (int $idx, string $s) {
            return \mb_substr($s, $idx, 1, 'UTF-8');
        };

        $res = '';

        $cap_next = $initCase;

        for ($i = 0; $i < $len; $i++) {
            $letter = $get_letter($i, $str);

            if ($letter >= 'A' && $letter <= 'Z') {
                $res .= $letter;
            }

            if ($letter >= '0' && $letter <= '9') {
                $res .= $letter;
            }

            if ($letter >= 'a' && $letter <= 'z') {
                if ($cap_next) {
                    $res .= mb_strtoupper($letter);
                } else {
                    $res .= $letter;
                }
            }

            if ($letter === '_' || $letter === ' ' || $letter === '-') {
                $cap_next = true;
            } else {
                $cap_next = false;
            }
        }

        return static::$delimitedCache[$str][$initCase] = $res;
    }

}
