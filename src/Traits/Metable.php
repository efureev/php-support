<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use Php\Support\Helpers\Arr;

/**
 * Trait Metable
 * @package Php\Support\Traits
 */
trait Metable
{
    /**
     * The meta data for the element.
     *
     * @var array
     */
    protected array $meta = [];

    /**
     * Get additional meta information to merge with the element payload.
     *
     * @return array
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function metaAttribute(string $key, mixed $default = null)
    {
        return Arr::get($this->meta, $key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param bool $removeNull
     *
     * @return $this
     */
    public function setMetaAttribute(string $key, mixed $value, bool $removeNull = false): static
    {
        if ($value !== null || !$removeNull) {
            Arr::set($this->meta, $key, $value);
        }

        return $this;
    }

    /**
     * Set additional meta information for the element.
     *
     * @param array $meta
     *
     * @return $this
     */
    public function withMeta(array $meta): static
    {
        $this->meta = Arr::merge($this->meta, $meta);

        return $this;
    }
}
