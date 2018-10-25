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
     * @param string     $message
     * @param mixed|null $config
     */
    public function __construct($message = 'Invalid Configuration', $config = null)
    {
        parent::__construct($message, $config);
    }
}
