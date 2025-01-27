<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use function mb_strlen;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function preg_replace;
use function trim;

class Str
{
    /**
     * The cache of delimited converted-cased words.
     *
     * @var array<string, mixed>
     */
    protected static array $delimitedCache = [];

    /**
     * Converts a string to snake_case
     */
    public static function toSnake(string $str): string
    {
        return self::toDelimited($str, '_');
    }

    /**
     * Converts a string to delimited.snake.case (in this case `del = '.'`)
     */
    public static function toDelimited(string $str, string $delimiter): string
    {
        return self::toScreamingDelimited($str, $delimiter, false);
    }

    /**
     * Converts a string to SCREAMING.DELIMITED.SNAKE.CASE (in this case `del = '.'; screaming = true`) or
     * delimited.snake.case (in this case `del = '.'; screaming = false`)
     */
    public static function toScreamingDelimited(string $str, string $delimiter, bool $screaming): string
    {
        $str = self::removeMultiSpace($str);
        $str = self::addWordBoundariesToNumbers($str);
        $str = trim($str);

        if (isset(static::$delimitedCache[$str][$delimiter][$screaming])) {
            return static::$delimitedCache[$str][$delimiter][$screaming];
        }

        $res = '';
        $len = mb_strlen($str, 'UTF-8');

        $get_letter = static function (int $idx, string $s) {
            return mb_substr($s, $idx, 1, 'UTF-8');
        };

        for ($i = 0; $i < $len; $i++) {
            // treat acronyms as words, eg for JSONData -> JSON is a whole word
            $next_case_is_changed = false;

            $letter = $get_letter($i, $str);

            if ($i + 1 < $len) {
                $next_letter = $get_letter($i + 1, $str);
                if (
                    (
                        $letter >= 'A'
                        && $letter <= 'Z'
                        && $next_letter >= 'a'
                        && $next_letter <= 'z'
                    )
                    || (
                        $letter >= 'a'
                        && $letter <= 'z'
                        && $next_letter >= 'A'
                        && $next_letter <= 'Z'
                    )
                ) {
                    $next_case_is_changed = true;
                }
            }

            if ($i > 0 && ($get_letter(mb_strlen($res, 'UTF-8') - 1, $res) !== $delimiter) && $next_case_is_changed) {
                // add underscore if next letter case type is changed
                if ($letter >= 'A' && $letter <= 'Z') {
                    $res .= $delimiter . $letter;
                } else {
                    if ($letter >= 'a' && $letter <= 'z') {
                        $res .= $letter . $delimiter;
                    }
                }
            } else {
                if ($letter === ' ' || $letter === '_' || $letter === '-') {
                    // replace spaces/underscores with delimiters
                    $res .= $delimiter;
                } else {
                    $res .= $letter;
                }
            }
        }

        if ($screaming) {
            $res = mb_strtoupper($res, 'UTF-8');
        } else {
            $res = mb_strtolower($res, 'UTF-8');
        }

        return static::$delimitedCache[$str][$delimiter][$screaming] = $res;
    }

    /**
     * Remove all multi-spaced characters
     *
     * @param string $str
     *
     * @return string
     */
    public static function removeMultiSpace(string $str): string
    {
        $res = preg_replace('/\s+/', ' ', $str);
        return is_string($res) ? $res : $str;
    }

    private static function addWordBoundariesToNumbers(string $str): string
    {
        $res = preg_replace('/([a-zA-Z])(\d+)([a-zA-Z]?)/u', '$1 $2 $3', $str);
        return is_string($res) ? $res : $str;
    }

    /**
     * Converts a string to SCREAMING_SNAKE_CASE
     */
    public static function toScreamingSnake(string $str): string
    {
        return self::toScreamingDelimited($str, '_', true);
    }

    /**
     * Converts a string to kebab-case
     */
    public static function toKebab(string $str): string
    {
        return self::toDelimited($str, '-');
    }

    /**
     * Converts a string to CamelCase
     */
    public static function toCamel(string $str): string
    {
        return self::toCamelInitCase($str, true);
    }

    /**
     * Converts a string to CamelCase
     */
    public static function toCamelInitCase(string $str, bool $initCase): string
    {
        $str = self::removeMultiSpace($str);
        $str = self::addWordBoundariesToNumbers($str);
        $str = trim($str);

        if (isset(static::$delimitedCache[$str][$initCase])) {
            return static::$delimitedCache[$str][$initCase];
        }


        $len = mb_strlen($str, 'UTF-8');

        $get_letter = static function (int $idx, string $s) {
            return mb_substr($s, $idx, 1, 'UTF-8');
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

    /**
     * Converts a string to lowerCamelCase
     */
    public static function toLowerCamel(string $str): string
    {
        if ($str === '') {
            return $str;
        }

        return self::toCamelInitCase(lcfirst($str), false);
    }

    /**
     * Replace substr by start and finish indents
     */
    public static function replaceStrTo(string $str, int $from_start, int $from_end, string $toStr = '*'): string
    {
        $from_start = $from_start < 0 ? 0 : $from_start;
        $from_end   = $from_end < 0 ? 0 : $from_end;
        $len        = mb_strlen($str);

        if ($from_start + $from_end >= $len) {
            return $str;
        }

        $start_str = mb_substr($str, 0, $from_start);
        $end_str   = $from_end ? mb_substr($str, -$from_end) : '';

        $replace_str = str_repeat($toStr, mb_strlen(mb_substr($str, $from_start, $len - $from_end - $from_start)));

        return $start_str . $replace_str . $end_str;
    }

    /**
     * Replace templates into string
     * Key = search value
     * Value = replace value
     *
     * @param string $str
     * @param array<string,string> $replace
     *
     * @return string|string[]
     */
    public static function replaceByTemplate(string $str, array $replace): array|string
    {
        return str_replace(array_keys($replace), array_values($replace), $str);
    }

    public static function isRegExp(string $regex): bool
    {
        return !empty($regex) && @preg_match($regex, '') !== false;
    }

    /**
     * Truncate a string to a specified length without cutting a word off
     */
    public static function truncate(string $str, int $length, string $append = '...'): string
    {
        $ret        = mb_substr($str, 0, $length);
        $last_space = mb_strrpos($ret, ' ');

        if ($last_space !== false && $str !== $ret) {
            $ret = mb_substr($ret, 0, $last_space);
        }

        if ($ret !== $str) {
            $ret .= $append;
        }

        return $ret;
    }


    /**
     * Generate a string safe for use in URLs from any given string.
     *
     * @param string $str
     * @param string $separator
     * @param bool $firstLetterOnly
     *
     * @return string
     */
    public static function slugify(string $str, string $separator = '-', bool $firstLetterOnly = false): string
    {
        return self::slugifyWithFormat($str, $separator, '([^a-z\d]+)', $firstLetterOnly);
    }

    public static function slugifyWithFormat(
        string $str,
        string $separator = '-',
        string $format = '([^a-z\d]+)',
        bool $firstLetterOnly = false
    ): string {
        $slug = preg_replace("/$format/", $separator, mb_strtolower(self::removeAccents($str)));
        if (empty($slug)) {
            return '';
        }

        if ($firstLetterOnly) {
            $digits = [
                'zero',
                'one',
                'two',
                'three',
                'four',
                'five',
                'six',
                'seven',
                'eight',
                'nine',
            ];

            if (is_numeric(mb_substr($slug, 0, 1))) {
                $slug = $digits[mb_substr($slug, 0, 1)] . mb_substr($slug, 1);
            }
        }

        return $slug;
    }


    /**
     * Checks to see if a string is utf8 encoded.
     *
     * NOTE: This function checks for 5-Byte sequences, UTF8
     *       has Bytes Sequences with a maximum length of 4.
     *
     * Written by Tony Ferrara <http://blog.ircmaxwell.com>
     *
     * @param string $string The string to be checked
     *
     * @return bool
     */
    public static function seemsUTF8(string $string): bool
    {
        return URLify::seemsUTF8($string);
    }

    /**
     * Converts all accent characters to ASCII characters.
     */
    public static function removeAccents(string $str, string $language = ''): string
    {
        if (!preg_match('/[\x80-\xff]/', $str)) {
            return $str;
        }

        return URLify::downcode($str, $language);
    }

    public static function trimPrefix(string $str, string $prefix): string
    {
        if (str_starts_with($str, $prefix)) {
            return mb_substr($str, mb_strlen($prefix));
        }

        return $str;
    }

    public static function trimSuffix(string $str, string $suffix): string
    {
        if (str_ends_with($str, $suffix)) {
            return mb_substr($str, 0, mb_strlen($str) - mb_strlen($suffix));
        }

        return $str;
    }
}
