<?php

declare(strict_types=1);

namespace Php\Support\Interfaces;

/**
 * Interface Arrayable
 *
 * @package Php\Support\Interfaces
 */
interface Arrayable
{
    /**
     * @return array
     */
    public function toArray(): array;
}
