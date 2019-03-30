<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class NotSupportedException
 *
 * @package Php\Support\Exceptions
 */
class NotSupportedException extends Exception
{
    /**
     * MissingClassException constructor.
     *
     * @param string|null $className
     * @param string $message
     */
    public function __construct($className = null, $message = 'Not Supported')
    {
        $message .= $className ? (': ' . $className) : '';
        parent::__construct($message);
    }
}
