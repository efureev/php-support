<?php

namespace Php\Support\Exceptions;

/**
 * Class UnknownMethodException
 *
 * @package Php\Support\Exceptions
 */
class UnknownMethodException extends \BadMethodCallException
{
    /** @var string|null */
    protected $method;

    /**
     * UnknownMethodException constructor.
     *
     * @param null|string $method
     * @param null|string $message
     */
    public function __construct(?string $message = null, ?string $method = null)
    {
        $this->method = $method;
        parent::__construct($message ?? ($this->getName() . ($this->method ? ': "' . $this->method . '"' : '')));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Unknown method';
    }

    /**
     * @return null|string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}
