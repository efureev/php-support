<?php
declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class MissingClassException
 *
 * @package Php\Support\Exceptions
 */
class MissingClassException extends Exception
{
    /**
     * MissingClassException constructor.
     *
     * @param string|null $className
     * @param string $message
     */
    public function __construct($className = null, $message = 'Missing Class')
    {
        $message .= $className ? (': ' . $className) : '';
        parent::__construct($message);
    }
}
