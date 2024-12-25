<?php

declare(strict_types=1);

namespace Php\Support;

use ArrayAccess;
use Countable;
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;
use JsonSerializable;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @mixin ArrayAccess<TKey, TValue>
 */
class Storage implements ArrayAccess, Countable, JsonSerializable
{
    /** @var array<TKey, TValue> */
    public private(set) array $data = [];

    /**
     * @param array<TKey, TValue> $init
     */
    public function __construct(array $init = [])
    {
        $this->data = $init;
    }

    public function set(string $key, mixed $value): void
    {
        Arr::set($this->data, $key, $value);
    }

    public function remove(string $key): void
    {
        Arr::remove($this->data, $key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    public function exist(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    public function __isset(string $name): bool
    {
        return $this->exist($name);
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __unset(string $name): void
    {
        $this->remove($name);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->exist($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function __toString(): string
    {
        return (string)Json::encode($this->data);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
