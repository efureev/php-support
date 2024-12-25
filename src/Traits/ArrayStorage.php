<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use ArrayAccess;
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;

/**
 * Class ArrayStorage
 *
 * @package Php\Support\Traits
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @mixin ArrayAccess<TKey, TValue>
 */
trait ArrayStorage // implements ArrayAccess, Arrayable
{
    /** @var array */
    private array $data = [];

    /** @var bool Показывать ошибку не взятие из get */
    protected bool $showErrorOnGetIfNull = true;

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function get(string $name): mixed
    {
        if ($this->propertyExists($name)) {
            return $this->$name;
        }

        if (Arr::has($this->data, $name)) {
            return Arr::get($this->data, $name);
        }

        if ($this->showErrorOnGetIfNull) {
            $trace = debug_backtrace();
            trigger_error(
                "Undefined property in __get(): $name in file {$trace[0]['file']} in line {$trace[0]['line']}",
                E_USER_NOTICE
            );
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function propertyExists(string $name): bool
    {
        return $name !== 'data' && property_exists($this, $name);
    }

    public function set(string $name, mixed $value): void
    {
        if ($this->propertyExists($name)) {
            $this->$name = $value;
            return;
        }

        Arr::set($this->data, $name, $value);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->propertyExists($name) || Arr::has($this->data, $name);
    }

    /**
     * @param string $name
     */
    public function __unset(string $name)
    {
        if ($this->propertyExists($name)) {
            $this->$name = null;
            return;
        }

        Arr::remove($this->data, $name);
    }

    /**
     * Determine if an item exists at an offset.
     */
    public function offsetExists(mixed $key): bool
    {
        return $this->valueExists($key);
    }

    public function valueExists(string $name): bool
    {
        return $this->propertyExists($name) || Arr::has($this->data, $name);
    }

    public function disableErrorOnNull(): static
    {
        $this->showErrorOnGetIfNull = false;

        return $this;
    }

    /**
     * Get an item at a given offset.
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set the item at a given offset.
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset the item at a given offset.
     */
    public function offsetUnset(mixed $key): void
    {
        unset($this->$key);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function __toString(): string
    {
        return (string)Json::encode($this->toArray());
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
