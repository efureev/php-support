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
    public function __construct(?string $message = null, ?string $property = null)
    {
        $this->property = $property;
        parent::__construct($message ?? ($this->getName() . ($this->property ? ': "' . $this->property . '"' : '')));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Unknown property';
    }

    /**
     * @return null|string
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }
}
