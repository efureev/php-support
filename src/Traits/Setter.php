<?php

namespace Php\Support\Traits;

use Php\Support\Exceptions\InvalidCallException;
use Php\Support\Exceptions\UnknownPropertyException;

/**
 * Trait Setter
 *
 * @package Php\Support\Traits
 */
trait Setter
{

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @throws \Php\Support\Exceptions\InvalidCallException
     * @throws \Php\Support\Exceptions\UnknownPropertyException
     */
    public function __set(string $name, $value)
    {
        $setter = static::setter($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . ucfirst($name))) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }


    /**
     * @param string $name
     */
    public function __unset(string $name)
    {
        $setter = static::setter($name);

        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . ucfirst($name))) {
            throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function setter(string $name)
    {
        return 'set' . ucfirst($name);
    }
}