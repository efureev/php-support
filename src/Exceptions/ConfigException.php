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
    public function __construct($message = 'Config Exception', $config = null)
    {
        parent::__construct($message);

        $this->config = $config;
    }

    /**
     * @return array|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }
}
