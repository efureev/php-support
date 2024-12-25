<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class MissingClassException
 */
class MissingClassException extends Exception
{
    public function __construct(string $class, string $message = 'Missing Class')
    {
        parent::__construct($message . ": $class");
    }
}
