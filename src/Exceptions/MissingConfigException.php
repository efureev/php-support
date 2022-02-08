<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class MissingConfigException
 *
 * @package Php\Support\Exceptions
 */
class MissingConfigException extends ConfigException
{
    /**
     * @param ?array $config
     * @param ?string $needKey
     * @param string $message
     */
    public function __construct(?array $config = null, protected ?string $needKey = null, $message = 'Missing Config')
    {
        parent::__construct($message, $config);
    }
}
