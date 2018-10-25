<?php

namespace Php\Support\Exceptions;

class InvalidParamException extends \BadMethodCallException
{

    public function getName()
    {
        return 'Invalid Parameter';
    }
}
