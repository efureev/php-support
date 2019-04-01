<?php

declare(strict_types=1);

namespace Php\Support\Candidates\Helpers;

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
    public static function arrayReplaceByTemplate(array &$array, array $replace): void
    {
        foreach ($array as &$item) {
            static::itemReplaceByTemplate($item, $replace);
        }
    }

    /**
     * Replace templates into item
     *
     * @param mixed $item
     * @param array $replace
     */
    private static function itemReplaceByTemplate(&$item, array $replace): void
    {
        if (is_array($item)) {
            self::arrayReplaceByTemplate($item, $replace);
        } else if (is_string($item)) {
            $item = Str::stringReplaceByTemplate($item, $replace);
        }
    }

    /**
     * @param string|null $s
     * @param int $start
     * @param null $end
     *
     * @return array
     */
    public static function fromPostgresArray(?string $s, $start = 0, &$end = null): array
    {
        if (empty($s) || $s[0] !== '{') {
            return [];
        }

        $return = [];
        $string = false;
        $quote = '';
        $len = strlen($s);
        $v = '';

        for ($i = $start + 1; $i < $len; $i++) {
            $ch = $s[$i];
            if (!$string && $ch === '}') {
                if ($v !== '' || !empty($return)) {
                    $return[] = $v;
                }
                $end = $i;
                break;
            } else {
                if (!$string && $ch === '{') {
                    $v = self::fromPostgresArray($s, $i, $i);
                } else
                    if (!$string && $ch === ',') {
                        $return[] = $v;
                        $v = '';
                    } else
                        if (!$string && ($ch === '"' || $ch === "'")) {
                            $string = true;
                            $quote = $ch;
                        } else
                            if ($string && $ch === $quote) {
                                if ($s[$i - 1] === "\\") {
                                    $v = substr($v, 0, -1) . $ch;
                                } else {
                                    $string = false;
                                }
                            } else {
                                $v .= $ch;
                            }
            }
        }

        foreach ($return as &$r) {
            if (is_numeric($r)) {
                if (ctype_digit((string)$r)) {
                    $r = (int)$r;
                } else {
                    $r = (float)$r;
                }
            }
        }

        return $return;
    }
}
