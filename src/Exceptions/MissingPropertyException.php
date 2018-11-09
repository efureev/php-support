<?php

namespace Php\Support\Exceptions;

/**
 * Class MissingPropertyException
 *
 * @package Php\Support\Exceptions
 */
class MissingPropertyException extends ConfigException
{
    /** @var string|null */
    protected $property;

    /**
     * MissingPropertyException constructor.
     *
     * @param null|string $message
     * @param null|string $property
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
        return 'Missing property';
    }

    /**
     * @return null|string
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }
}
