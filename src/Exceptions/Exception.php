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
