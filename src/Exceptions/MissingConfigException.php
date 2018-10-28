<?php

namespace Php\Support\Exceptions;

/**
 * Class MissingConfigException
 *
 * @package Php\Support\Exceptions
 */
class MissingConfigException extends ConfigException
{
    /** @var string|null */
    protected $needKey;

    /**
     * MissingConfigException constructor.
     *
     * @param mixed  $config
     * @param string $needKey
     * @param string $message
     */
    public function __construct($config = null, $needKey = null, $message = 'Missing Config')
    {
        parent::__construct($config, $message);
        $this->needKey = $needKey;
    }
}
