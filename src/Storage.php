<?php

declare(strict_types=1);

namespace Php\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Arrayable;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 * @implements Arrayable<TKey, TValue>
 * @mixin ArrayAccess<TKey, TValue>
 *
 * @phpstan-consistent-constructor
 */
class Storage implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Arrayable
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

    /**
     * @param non-empty-string $separator
     */
    public function set(string $key, mixed $value, string $separator = '.'): void
    {
        Arr::set($this->data, $key, $value, $separator);
    }

    /**
     * @param non-empty-string $separator
     */
    public function remove(string $key, string $separator = '.'): void
    {
        Arr::remove($this->data, $key, $separator);
    }

    /**
     * @param non-empty-string $separator
     */
    public function get(string $key, mixed $default = null, string $separator = '.'): mixed
    {
        return Arr::get($this->data, $key, $default, $separator);
    }

    /**
     * @param non-empty-string $separator
     */
    public function exist(string $key, string $separator = '.'): bool
    {
        return Arr::has($this->data, $key, $separator);
    }

    /**
     * Gets a native PHP array of the stored data.
     *
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Checks whether the storage holds no data.
     */
    public function isEmpty(): bool
    {
        return $this->data === [];
    }

    /**
     * Removes every stored value.
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Merge the given data in, recursively. Existing keys are overwritten.
     *
     * @param array<array-key, mixed>|self<array-key, mixed> $data
     */
    public function merge(array|self $data): static
    {
        $this->data = Arr::merge($this->data, $data instanceof self ? $data->all() : $data);

        return $this;
    }

    /**
     * A new storage holding only the given top-level keys.
     *
     * @param string[]|string $keys
     *
     * @return static
     */
    public function only(array|string $keys): static
    {
        return new static(Arr::only($this->data, (array)$keys));
    }

    /**
     * A new storage without the given top-level keys.
     *
     * @param string[]|string $keys
     *
     * @return static
     */
    public function except(array|string $keys): static
    {
        return new static(Arr::except($this->data, (array)$keys));
    }

    /**
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
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
        $this->set((string)$offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * Number of top-level keys. Nested values created through the dot notation are not counted;
     * use {@see self::countRecursive()} for that.
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Number of scalar (non-array) values, at any depth.
     *
     * @return int<0, max>
     */
    public function countRecursive(): int
    {
        $count = static function (array $data) use (&$count): int {
            $result = 0;

            foreach ($data as $value) {
                $result += is_array($value) ? $count($value) : 1;
            }

            return $result;
        };

        return $count($this->data);
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
