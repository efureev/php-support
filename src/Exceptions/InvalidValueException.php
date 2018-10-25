<?php

namespace Php\Support\Exceptions;

class InvalidValueException extends \UnexpectedValueException
{
    public function getName()
    {
        return 'Invalid Return Value';
    }
}
