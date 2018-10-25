<?php

namespace Php\Support\Exceptions;

/**
 * Class MethodNotAllowedException
 *
 * @package Php\Support\Exceptions
 */
class MethodNotAllowedException extends Exception
{
    /** @var string */
    protected $reason;

    public function __construct(string $reason, $message = 'Method Not Allowed')
    {
        parent::__construct($message);
        $this->reason = $reason;
    }
}
