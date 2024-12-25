<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait ArrayStorageConfigurableTrait
 *
 * @package Php\Support\Traits
 */
trait ArrayStorageConfigurableTrait
{
    use ArrayStorage;
    use ConfigurableTrait {
        ArrayStorage::propertyExists insteadof ConfigurableTrait;
    }

    protected function setPropConfigurable(string $key, mixed $value): bool
    {
        $this->set($key, $value);

        return true;
    }
}
