<?php

namespace Php\Support\Traits;

use ArrayAccess;
use Php\Support\Exceptions\InvalidParamException;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @mixin ArrayAccess<TKey, TValue>
 */
trait ConfigurableTrait
{
    public function configurable(array|ArrayAccess $attributes, bool $throwOnMissingProp = true): static
    {
        foreach ($attributes as $key => $value) {
            if (!$this->applyValue($key, $value) && $throwOnMissingProp) {
                throw new InvalidParamException("Property $key is absent at class: " . $this::class);
            }
        }

        return $this;
    }

    protected function applyValue(string $key, mixed $value): bool
    {
        return $this->callSetterProp($key, $value) || $this->setPropValue($key, $value);
    }

    protected function setPropValue(string $key, mixed $value): bool
    {
        if ($this->propertyExists($key)) {
            $this->{$key} = $value;

            return true;
        }

        return false;
    }

    protected function propertyExists(string $name): bool
    {
        return property_exists($this, $name);
    }

    protected function callSetterProp(string $key, mixed $value): bool
    {
        if ($method = findSetterMethodByProp($this, $key)) {
            $this->$method($value);

            return true;
        }

        return false;
    }
}
