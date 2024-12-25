<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class ConfigException
 */
class ConfigException extends Exception
{
    /**
     * @param string $message
     * @param array<string, mixed> $config
     */
    public function __construct(string $message = 'Config Exception', protected(set) array $config = [])
    {
        parent::__construct($message);
    }
}
