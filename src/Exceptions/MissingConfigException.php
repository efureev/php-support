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
    public function __construct(array $config = [], protected ?string $needKey = null, string $message = 'Missing Config')
    {
        parent::__construct($message, $config);
    }
}
