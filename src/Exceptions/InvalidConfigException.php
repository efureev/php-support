<?php

namespace Php\Support\Exceptions;

/**
 * Class InvalidConfigException
 *
 * @package Php\Support\Exceptions
 */
class InvalidConfigException extends ConfigException
{
    /**
     * InvalidConfigException constructor.
     *
     * @param mixed|null $config
     * @param string     $message
     */
    public function __construct($config = null, $message = 'Invalid Configuration')
    {
        parent::__construct($config, $message);
    }
}
