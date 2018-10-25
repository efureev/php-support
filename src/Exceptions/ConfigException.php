<?php

namespace Php\Support\Exceptions;

/**
 * Class ConfigException
 *
 * @package Php\Support\Exceptions
 */
class ConfigException extends Exception
{
    /**
     * @var mixed
     */
    protected $config;

    /**
     * ConfigException constructor.
     *
     * @param string $message
     * @param null   $config
     */
    public function __construct($message = 'Config Exception', $config = null)
    {
        parent::__construct($message);

        $this->config = $config;
    }
}
