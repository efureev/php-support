<?php

namespace Php\Support\Exceptions;

/**
 * Class InvalidArgumentException
 *
 * @package Php\Support\Exceptions
 */
class InvalidArgumentException extends \BadMethodCallException
{

    /**
     * @return string
     */
    public function getName()
    {
        return 'Invalid Argument';
    }

    /**
     * Exception constructor.
     *
     * @param null|string     $message
     * @param int|null        $code
     * @param \Throwable|null $previous
     */
    public function __construct(?string $message = null, ?int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?? $this->getName(), $code, $previous);
    }
}
