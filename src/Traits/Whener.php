<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait Whener
 * @package Php\Support\Traits
 */
trait Whener
{
    public function when(mixed $value, callable $callback, ?callable $default = null): mixed
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        }

        if ($default) {
            return $default($this, $value) ?: $this;
        }

        return $this;
    }
}
