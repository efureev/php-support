<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use ArrayAccess;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 * @mixin ArrayAccess<TKey, TValue>
 */
trait UseConfigurableStorage
{
    use UseStorage;
    use ConfigurableTrait {
        UseStorage::propertyExists insteadof ConfigurableTrait;
        ConfigurableTrait::applyValue as applyValueToProps;
    }

    /**
     * Applies a value to a real property (or its setter) and falls back to the storage.
     */
    protected function applyValue(string $key, mixed $value): bool
    {
        return $this->applyValueToProps($key, $value) || $this->configureProps($key, $value);
    }

    protected function configureProps(string $key, mixed $value): bool
    {
        $this->set($key, $value);

        return true;
    }
}
