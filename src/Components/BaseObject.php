<?php

namespace Php\Support\Components;

use Php\Support\Exceptions\InvalidCallException;
use Php\Support\Exceptions\UnknownMethodException;
use Php\Support\Exceptions\UnknownPropertyException;

class BaseObject
{
    public static function className()
    {
        return get_called_class();
    }

    /**
     * @param string $className
     *
     * @return mixed
     */
    public static function shortClassName($className = null)
    {
        $path = explode('\\', $className ? $className : get_called_class());

        return array_pop($path);
    }

    /**
     * @param $name
     *
     * @return mixed
     * @throws \Php\Support\Exceptions\InvalidCallException
     * @throws \Php\Support\Exceptions\UnknownPropertyException
     */
    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException($name, 'Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws \Php\Support\Exceptions\InvalidCallException
     * @throws \Php\Support\Exceptions\UnknownPropertyException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }

    /**
     * @param string $name
     */
    public function __unset(string $name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * @param string $name
     * @param mixed  $params
     *
     * @throws \Php\Support\Exceptions\UnknownMethodException
     */
    public function __call(string $name, $params)
    {
        throw new UnknownMethodException($name, 'Calling unknown method: ' . get_class($this) . "::$name()");
    }


    /**
     * @param string $name
     * @param bool   $checkVars
     *
     * @return bool
     */
    public function hasProperty(string $name, bool $checkVars = true): bool
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }


    /**
     * @param string $name
     * @param bool   $checkVars
     *
     * @return bool
     */
    public function canGetProperty(string $name, bool $checkVars = true): bool
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * @param string $name
     * @param bool   $checkVars
     *
     * @return bool
     */
    public function canSetProperty(string $name, bool $checkVars = true): bool
    {
        return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasMethod(string $name): bool
    {
        return method_exists($this, $name);
    }
}
