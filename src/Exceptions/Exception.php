<?php

namespace Php\Support\Exceptions;
/**
 * Class Exception
 *
 * @package Php\Support\Exceptions
 */
class Exception extends \Exception
{
    public function getName()
    {
        return 'Exception';
    }
}
