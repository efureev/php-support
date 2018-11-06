<?php

namespace Php\Support\Exceptions;

/**
 * Class UnknownPropertyException
 *
 * @package Php\Support\Exceptions
 */
class UnknownPropertyException extends Exception
{
    /** @var string|null */
    protected $property;

    /**
     * UnknownPropertyException constructor.
     *
     * @param null|string $property
     * @param null|string $message
     */
    public function __construct(?string $property = null, ?string $message = null)
    {
        $this->property = $property;
        parent::__construct($message ?? sprintf('Missing property "%s" ', $this->property));
    }

    /**
     * @return null|string
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }
}
