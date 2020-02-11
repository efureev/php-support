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
    use ArrayStorage, ConfigurableTrait {
        ArrayStorage::propertyExists insteadof ConfigurableTrait;
    }

    /**
     * @param string $key
     * @param $value
     *
     * @return bool
     */
    protected function setPropConfigurable(string $key, $value): bool
    {
        $this->set($key, $value);

        return true;
    }
}
