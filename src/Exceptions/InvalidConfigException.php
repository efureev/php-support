<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class InvalidConfigException
 */
class InvalidConfigException extends ConfigException
{
    public function __construct(array $config = [], string $message = 'Invalid Configuration')
    {
        parent::__construct($message, $config);
    }
}
