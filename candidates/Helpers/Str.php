<?php

declare(strict_types=1);

namespace Php\Support\Candidates\Helpers;

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
     * @param array $replace
     *
     * @return mixed
     */
    public static function stringReplaceByTemplate(string $str, array $replace)
    {
        return str_replace(array_keys($replace), array_values($replace), $str);
    }
}
