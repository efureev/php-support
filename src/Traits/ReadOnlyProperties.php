<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use Php\Support\Exceptions\MissingPropertyException;

trait ReadOnlyProperties
{
    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }

        throw new MissingPropertyException(null, $key);
    }
}
