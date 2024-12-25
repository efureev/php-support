<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class UnknownPropertyException
 */
class UnknownPropertyException extends Exception
{
    public function __construct(protected(set) string $property, ?string $message = null)
    {
        parent::__construct($message ?? ($this->getName() . ": $this->property"));
    }

    public function getName(): string
    {
        return 'Unknown property';
    }
}
