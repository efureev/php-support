<?php

namespace Php\Support\Exceptions;

/**
 * Class MissingConfigException
 *
 * @package Php\Support\Exceptions
 */
class MissingConfigException extends ConfigException
{
    /** @var string */
    protected $needKey;

    /**
     * MissingConfigException constructor.
     *
     * @param string $message
     * @param null   $config
     */
    public function __construct($message = 'Missing Config', $needKey = null, $config = null)
    {
        parent::__construct($message, $config);
        $this->needKey = $needKey;
    }
}
