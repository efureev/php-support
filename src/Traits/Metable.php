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
    protected $meta = [];

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
     * Set additional meta information for the element.
     *
     * @param array $meta
     *
     * @return $this
     */
    public function withMeta(array $meta): self
    {
        $this->meta = Arr::merge($this->meta, $meta);

        return $this;
    }
}
