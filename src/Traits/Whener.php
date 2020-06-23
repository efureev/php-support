<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait Whener
 * @package Php\Support\Traits
 */
trait Whener
{
    /**
     * @param mixed $value
     * @param callable $callback
     * @param null|callable $default
     *
     * @return $this
     */
    public function when($value, callable $callback, ?callable $default = null): self
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
