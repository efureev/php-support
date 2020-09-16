<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use ArrayAccess;
use Php\Support\Exceptions\JsonException;
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;

/**
 * Class ArrayStorage
 *
 * @package Php\Support\Traits
 * @mixin ArrayAccess
 */
trait ArrayStorage // implements ArrayAccess, Arrayable
{
    /** @var array */
    private $data = [];

    /** @var bool Показывать ошибку не взятие из get */
    protected $showErrorOnGetIfNull = true;

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function get(string $name)
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

    /**
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value): void
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
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->valueExists($key);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function valueExists(string $name): bool
    {
        return $this->propertyExists($name) || Arr::has($this->data, $name);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $key
     *
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->$key);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->getData());
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function __toString(): string
    {
        return (string)Json::encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->getData();
    }
}
