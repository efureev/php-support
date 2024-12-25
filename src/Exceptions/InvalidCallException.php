<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use BadMethodCallException;

/**
 * Class InvalidCallException
 */
class InvalidCallException extends BadMethodCallException
{
    public function getName(): string
    {
        return 'Invalid Call';
    }
}
