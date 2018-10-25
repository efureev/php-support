<?php

namespace Php\Support\Exceptions;

class InvalidCallException extends \BadMethodCallException
{
    public function getName()
    {
        return 'Invalid Call';
    }
}
