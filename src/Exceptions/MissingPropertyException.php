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
     * @param string $message
     */
    public function __construct($config = null, $property = null, $message = 'Missing Property in Config')
    {
        parent::__construct($config, $message);
        $this->property = $property;
    }
}
