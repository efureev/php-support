<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use Php\Support\Exceptions\InvalidParamException;

use function mb_strlen;
use function mb_strpos;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function preg_replace;
use function trim;

class Str
{
    /**
     * How many distinct source strings each conversion cache keeps.
     *
     * The caches are static, so without a bound they grow for the lifetime of the process -
     * a leak in workers and long-running CLI processes that convert user input.
     */
    public const int CACHE_LIMIT = 1000;

    /**
     * The cache of delimited converted-cased words.
     *
     * @var array<string, mixed>
     */
    protected static array $delimitedCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array<string, mixed>
     */
    protected static array $camelCache = [];

    /**
     * Drops both conversion caches.
     *
     * Useful in long-running processes and between test cases.
     */
    public static function clearCache(): void
    {
        static::$delimitedCache = [];
        static::$camelCache     = [];
    }

    /**
     * Evicts the oldest half of a cache once it outgrows {@see self::CACHE_LIMIT}.
     *
     * @param array<string, mixed> $cache
     * @param-out array<string, mixed> $cache
     */
    private static function evictCache(array &$cache): void
    {
        if (count($cache) < self::CACHE_LIMIT) {
            return;
        }

        $cache = array_slice($cache, (int)(self::CACHE_LIMIT / 2), null, true);
    }

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

        self::evictCache(static::$delimitedCache);

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

        if (isset(static::$camelCache[$str][$initCase])) {
            return static::$camelCache[$str][$initCase];
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

        self::evictCache(static::$camelCache);

        return static::$camelCache[$str][$initCase] = $res;
    }

    /**
     * Converts a string to lowerCamelCase
     */
    public static function toLowerCamel(string $str): string
    {
        if ($str === '') {
            return $str;
        }

        $first = mb_strtolower(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8');
        $str   = $first . mb_substr($str, 1, null, 'UTF-8');

        return self::toCamelInitCase($str, false);
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
     * Truncate a string to a specified length, trimming at the last whole word when possible.
     *
     * Note: if the truncated part contains no space (a single long word), the word is cut
     * at the exact $length boundary.
     */
    public static function truncate(string $str, int $length, string $append = '...'): string
    {
        if ($length < 1) {
            throw new InvalidParamException("Length must be a positive integer, $length given", 'length');
        }

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
        $pattern = "/$format/";

        if (!self::isRegExp($pattern)) {
            throw new InvalidParamException("Invalid slug format: $format", 'format');
        }

        $slug = preg_replace($pattern, $separator, mb_strtolower(self::removeAccents($str)));

        if ($slug === null) {
            throw new InvalidParamException("Slug format failed to apply: $format", 'format');
        }

        if ($slug === '') {
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

    /**
     * Generate a cryptographically secure random alpha-numeric string.
     *
     * @param int<1, max> $length
     *
     * @throws InvalidParamException
     */
    public static function random(int $length = 16): string
    {
        if ($length < 1) {
            throw new InvalidParamException("Length must be a positive integer, $length given", 'length');
        }

        $result = '';

        while (strlen($result) < $length) {
            $missing = $length - strlen($result);
            $bytes   = random_bytes((int)ceil($missing / 3) * 3);
            $result .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $missing);
        }

        return $result;
    }

    /**
     * Mask a portion of a string with a repeated character.
     *
     * @param string $str
     * @param string $character Replacement character; an empty one leaves the string untouched.
     * @param int $index Where to start masking; negative counts from the end.
     * @param ?int $length How many characters to mask; null means "to the end".
     */
    public static function mask(string $str, string $character = '*', int $index = 0, ?int $length = null): string
    {
        if ($character === '' || $str === '') {
            return $str;
        }

        $strLength = mb_strlen($str);
        $start     = $index < 0 ? max($strLength + $index, 0) : min($index, $strLength);
        $segment   = mb_substr($str, $start, $length);

        if ($segment === '') {
            return $str;
        }

        $head = mb_substr($str, 0, $start);
        $body = str_repeat(mb_substr($character, 0, 1), mb_strlen($segment));
        $tail = mb_substr($str, $start + mb_strlen($segment));

        return $head . $body . $tail;
    }

    /**
     * Truncate a string to a given number of characters, without word awareness.
     *
     * Unlike {@see self::truncate()} this never trims at a word boundary.
     *
     * @throws InvalidParamException
     */
    public static function limit(string $str, int $length = 100, string $append = '...'): string
    {
        if ($length < 1) {
            throw new InvalidParamException("Length must be a positive integer, $length given", 'length');
        }

        if (mb_strlen($str) <= $length) {
            return $str;
        }

        return mb_substr($str, 0, $length) . $append;
    }

    /**
     * Determine whether the string contains any of the given substrings.
     *
     * @param string|string[] $needles
     */
    public static function contains(string $haystack, string|array $needles, bool $ignoreCase = false): bool
    {
        if ($ignoreCase) {
            $haystack = mb_strtolower($haystack, 'UTF-8');
        }

        foreach ((array)$needles as $needle) {
            if ($needle === '') {
                continue;
            }

            if (str_contains($haystack, $ignoreCase ? mb_strtolower($needle, 'UTF-8') : $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the string starts with any of the given substrings.
     *
     * @param string|string[] $needles
     */
    public static function startsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the string ends with any of the given substrings.
     *
     * @param string|string[] $needles
     */
    public static function endsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Collapse repeated whitespace and trim the result.
     *
     * {@see self::removeMultiSpace()} only collapses; this also trims the ends.
     */
    public static function squish(string $str): string
    {
        return trim(self::removeMultiSpace($str));
    }

    /**
     * Return everything after the first occurrence of $search.
     *
     * Returns the whole string when $search is empty or not found.
     */
    public static function after(string $str, string $search): string
    {
        if ($search === '') {
            return $str;
        }

        $position = mb_strpos($str, $search);

        return $position === false ? $str : mb_substr($str, $position + mb_strlen($search));
    }

    /**
     * Return everything before the first occurrence of $search.
     *
     * Returns the whole string when $search is empty or not found.
     */
    public static function before(string $str, string $search): string
    {
        if ($search === '') {
            return $str;
        }

        $position = mb_strpos($str, $search);

        return $position === false ? $str : mb_substr($str, 0, $position);
    }

    /**
     * Return the substring between the first $from and the following $to.
     *
     * Returns an empty string when either delimiter is missing.
     */
    public static function between(string $str, string $from, string $to): string
    {
        if ($from === '' || $to === '') {
            return '';
        }

        $start = mb_strpos($str, $from);

        if ($start === false) {
            return '';
        }

        $start += mb_strlen($from);
        $end    = mb_strpos($str, $to, $start);

        return $end === false ? '' : mb_substr($str, $start, $end - $start);
    }

    /**
     * Multibyte-aware ucfirst().
     */
    public static function ucFirst(string $str): string
    {
        if ($str === '') {
            return $str;
        }

        return mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($str, 1, null, 'UTF-8');
    }

    /**
     * Multibyte-aware lcfirst().
     */
    public static function lcFirst(string $str): string
    {
        if ($str === '') {
            return $str;
        }

        return mb_strtolower(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($str, 1, null, 'UTF-8');
    }
}
