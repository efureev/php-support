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
     * MissingConfigException constructor.
     *
     * @param mixed  $config
     * @param string $property
     */
    public function __construct($config = null, $property = null)
    {
        $this->property = $property;
        parent::__construct($config, sprintf('Missing property "%s" in config', $this->property));
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }
}
