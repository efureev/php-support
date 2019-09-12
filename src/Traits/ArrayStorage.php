<?php
declare(strict_types=1);

namespace Php\Support\Traits;

use Php\Support\Helpers\Arr;

/**
 * Class ArrayStorage
 *
 * @package Php\Support\Traits
 */
trait ArrayStorage
{
    /** @var array */
    private $data = [];

    private function propertyExists($name): bool
    {
        return $name !== 'data' && property_exists($this, $name);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get(string $name)
    {
        if ($this->propertyExists($name)) {
            return $this->$name;
        }

        if (array_key_exists($name, $this->data)) {
            $val = Arr::get($this->data, $name);
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property in __get(): ' . $name .
            ' in file ' . $trace[0]['file'] .
            ' in line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        if ($this->propertyExists($name)) {
            $this->$name = $value;
        }

        $this->data[$name] = $value;
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
        } else {

            unset($this->data[$name]);
        }
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
