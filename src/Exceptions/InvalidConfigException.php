<?php

declare(strict_types=1);

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
     * @param ?array $config
     * @param string $message
     */
    public function __construct(?array $config = null, $message = 'Invalid Configuration')
    {
        parent::__construct($message, $config);
    }
}
