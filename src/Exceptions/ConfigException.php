<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class ConfigException
 *
 * @package Php\Support\Exceptions
 */
class ConfigException extends Exception
{
    /**
     * ConfigException constructor.
     *
     * @param ?array $config
     * @param string $message
     */
    public function __construct(string $message = 'Config Exception', protected ?array $config = null)
    {
        parent::__construct($message);
    }

    /**
     * @return array|null
     */
    public function getConfig(): ?array
    {
        return $this->config;
    }
}
