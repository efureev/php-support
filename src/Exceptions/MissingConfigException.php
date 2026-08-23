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
    public function __construct(
        array $config = [],
        protected(set) ?string $needKey = null,
        string $message = 'Missing Config'
    ) {
        parent::__construct($this->needKey === null ? $message : "$message: $this->needKey", $config);
    }
}
