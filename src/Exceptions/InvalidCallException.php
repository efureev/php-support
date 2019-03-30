<?php

namespace Php\Support\Exceptions;

use BadMethodCallException;

/**
 * Class InvalidCallException
 * @package Php\Support\Exceptions
 */
class InvalidCallException extends BadMethodCallException
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Invalid Call';
    }
}
