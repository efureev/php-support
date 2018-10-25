<?php

namespace Php\Support\Exceptions;

/**
 * Class InvalidConfigException
 *
 * @package Php\Support\Exceptions
 */
class InvalidConfigException extends Exception
{
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
