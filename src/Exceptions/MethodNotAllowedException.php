<?php
declare(strict_types=1);

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

    /**
     * MethodNotAllowedException constructor.
     *
     * @param string $reason
     * @param string $message
     */
    public function __construct(string $reason, $message = 'Method Not Allowed')
    {
        $this->reason = $reason;
        parent::__construct($message);
    }
}
