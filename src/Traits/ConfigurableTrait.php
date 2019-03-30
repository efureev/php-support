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
            if (!$this->setProp($key, $value) && $exceptOnMiss) {
                throw new InvalidParamException("Property $key is absent at class: " . get_class($this));
            }
        }

        return $this;
    }

    /**
     * Set properties to object
     *
     * @param string $key
     * @param        $value
     *
     * @return bool
     */
    private function setProp(string $key, $value): bool
    {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;

            return true;
        }

        return false;
    }
}
