<?php

namespace Php\Support\Interfaces;

/**
 * Interface Jsonable
 *
 * @package Php\Support\Interfaces
 */
interface Jsonable
{
    /**
     * @static
     *
     * @param string $string
     *
     * @return Jsonable
     */
    public static function fromJson(string $string): Jsonable;

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 320);
}
