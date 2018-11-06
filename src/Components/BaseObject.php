<?php

namespace Php\Support\Components;

use Php\Support\Exceptions\UnknownMethodException;
use Php\Support\Traits\{Getter, Setter};

class BaseObject
{
    use Getter;
    use Setter;

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
