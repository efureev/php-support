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
trait UseConfigurabeStorage
{
    use UseStorage;
    use ConfigurableTrait {
        UseStorage::propertyExists insteadof ConfigurableTrait;
    }

    protected function configureProps(string $key, mixed $value): bool
    {
        $this->set($key, $value);

        return true;
    }
}
