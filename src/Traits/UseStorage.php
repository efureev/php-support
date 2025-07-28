<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use ArrayAccess;
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;
use Php\Support\Storage;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @mixin ArrayAccess<TKey, TValue>
 */
trait UseStorage
{
    private Storage $storage {
        get {
            return $this->storage ??= new Storage();
        }
    }

    protected function propertyExists(string $name): bool
    {
        return $name !== 'storage' && property_exists($this, $name);
    }


    public function set(string $name, mixed $value): void
    {
        if ($this->propertyExists($name)) {
            $this->$name = $value;
            return;
        }

        $this->storage->set($name, $value);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if ($this->propertyExists($name)) {
            return $this->$name;
        }

        return $this->storage->get($name, $default);
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->propertyExists($name) || $this->storage->exist($name);
    }

    public function __unset(string $name): void
    {
        if ($this->propertyExists($name)) {
            $this->$name = null;
            return;
        }

        $this->storage->remove($name);
    }

    public function offsetExists(mixed $key): bool
    {
        return $this->propExists($key);
    }

    public function propExists(string $name): bool
    {
        return $this->propertyExists($name) || $this->storage->exist($name);
    }

    public function offsetGet(mixed $key): mixed
    {
        return $this->get($key);
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function offsetUnset(mixed $key): void
    {
        unset($this->$key);
    }
}
