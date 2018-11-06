<?php

namespace Php\Support\Interfaces;

/**
 * Interface Arrayable
 *
 * @package Php\Support\Interfaces
 */
interface Arrayable
{
    /**
     * @param array $fields
     *
     * @return array
     */
    public function toArray(array $fields = []);
}
