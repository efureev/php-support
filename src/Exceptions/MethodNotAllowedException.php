<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class MethodNotAllowedException
 */
class MethodNotAllowedException extends Exception
{
    public function __construct(protected string $reason, string $message = 'Method Not Allowed')
    {
        $this->reason = $reason;

        parent::__construct($message ? "$message: $reason" : $reason);
    }
}
