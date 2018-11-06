<?php

namespace Php\Support\Exceptions;

/**
 * Class UnknownMethodException
 *
 * @package Php\Support\Exceptions
 */
class UnknownMethodException extends Exception
{
    /** @var string|null */
    protected $method;

    /**
     * UnknownMethodException constructor.
     *
     * @param null|string $method
     * @param null|string $message
     */
    public function __construct(?string $method = null, ?string $message = null)
    {
        $this->method = $method;
        parent::__construct($message ?? sprintf('Missing method "%s" ', $this->method));
    }

    /**
     * @return null|string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}
