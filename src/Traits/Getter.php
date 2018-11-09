<?php

namespace Php\Support\Traits;

use Php\Support\Exceptions\InvalidCallException;
use Php\Support\Exceptions\UnknownPropertyException;

/**
 * Trait Getter
 *
 * @package Php\Support\Traits
 */
trait Getter
{
    /**
     * @param string $name
     *
     * @return mixed
     * @throws \Php\Support\Exceptions\InvalidCallException
     * @throws \Php\Support\Exceptions\UnknownPropertyException
     */
    public function __get(string $name)
    {
        $getter = static::getter($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . ucfirst($name))) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name, $name);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function getter(string $name)
    {
        return 'get' . ucfirst($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name)
    {
        $getter = static::getter($name);
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }
}