<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use Php\Support\Exceptions\MissingPropertyException;

trait UseSetter
{
    public function callSetter(string $key): void
    {
        if (method_exists($this, $method = 'set' . ucfirst($key))) {
            $this->$method($value);

            return true;
        }

        if (property_exists($this, $key)) {
            return $this->$key;
        }

        throw new MissingPropertyException(null, $key);
    }
}
