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
     * @param null   $config
     * @param string $message
     */
    public function __construct($config = null, $message = 'Config Exception')
    {
        parent::__construct($message);

        $this->config = $config;
    }
}
