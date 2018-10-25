<?php

namespace Php\Support\Exceptions;

class InvalidArgumentException extends \BadMethodCallException
{

    public function getName()
    {
        return 'Invalid Argument';
    }
}
