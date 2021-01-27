<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use ArrayAccess;
use Php\Support\Exceptions\InvalidParamException;

/**
 * Trait ConfigurableTrait
 * @package Php\Support\Traits
 */
trait ConfigurableTrait
{
    /**
     * @param array|ArrayAccess $attributes
     * @param bool $exceptOnMiss
     *
     * @return $this
     */
    public function configurable($attributes, ?bool $exceptOnMiss = true)
    {
        foreach ($attributes as $key => $value) {
            if (!$this->applyValue($key, $value) && $exceptOnMiss) {
                throw new InvalidParamException("Property $key is absent at class: " . get_class($this));
            }
        }

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return bool
     */
    protected function applyValue(string $key, $value): bool
    {
        if (!$res = $this->callMethod($key, $value)) {
            $res = $this->setPropConfigurable($key, $value);
        }
        return $res;
    }

    /**
     * @param string $key
     * @param        $value
     *
     * @return bool
     */
    protected function setPropConfigurable(string $key, $value): bool
    {
        if ($this->propertyExists($key)) {
            $this->{$key} = $value;
            return true;
        }

        return false;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function propertyExists(string $key): bool
    {
        return property_exists($this, $key);
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return bool
     */
    protected function callMethod(string $key, $value): bool
    {
        if (method_exists($this, $method = 'set' . ucfirst($key))) {
            $this->$method($value);

            return true;
        }

        return false;
    }
}
