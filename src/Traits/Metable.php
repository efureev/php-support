<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use Php\Support\Helpers\Arr;

/**
 * @template TValue
 */
trait Metable
{
    /**
     * The metadata for the element.
     *
     * @var array<string, TValue>
     */
    protected array $meta = [];

    /**
     * Get additional meta information to merge with the element payload.
     *
     * @return array<string, TValue>
     */
    public function meta(): array
    {
        return $this->meta;
    }

    public function metaAttribute(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->meta, $key, $default);
    }

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
     * @param array<string, TValue> $meta
     */
    public function withMeta(array $meta): static
    {
        $this->meta = Arr::merge($this->meta, $meta);

        return $this;
    }
}
