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
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 320);

    /**
     * @param string $string
     *
     * @return array
     */
    public function fromJson(string $string): array;
}
