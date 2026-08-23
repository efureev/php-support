<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use ArrayAccess;
use Php\Support\Exceptions\InvalidParamException;

/**
 * Configures an object from a map of attributes, through setters or declared properties.
 *
 * An unknown key is an error by default. If you want unknown keys to be kept instead of rejected,
 * use {@see UseConfigurableStorage}, which adds a {@see \Php\Support\Storage} fallback on top of
 * this trait rather than replacing it.
 *
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
            if (!$this->applyValue((string)$key, $value) && $throwOnMissingProp) {
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
