<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait Thrower
 * @package Php\Support\Traits
 */
trait Thrower
{
    /**
     * Throw Exception
     *
     * @param mixed ...$arguments
     */
    public static function throw(...$arguments): void
    {
        throw new static(...$arguments);
    }
}
