<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use Php\Support\Exceptions\InvalidParamException;

/**
 * Trait ConfigurableTrait
 * @package Php\Support\Traits
 */
trait ConfigurableTrait
{
    /**
     * @param array $attributes
     * @param bool $exceptOnMiss
     *
     * @return $this
     */
    public function configurable(array $attributes, ?bool $exceptOnMiss = true): self
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
     * @param        $value
     *
     * @return bool
     */
    protected function setProp(string $key, $value): bool
    {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;

            return true;
        }

        return false;
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

    /**
     * @param string $key
     * @param $value
     *
     * @return bool
     */
    protected function applyValue(string $key, $value): bool
    {
        return $this->setProp($key, $value) || $this->callMethod($key, $value);
    }
}
