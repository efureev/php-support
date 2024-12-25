<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use BadMethodCallException;

/**
 * Class UnknownMethodException
 */
class UnknownMethodException extends BadMethodCallException
{
    public function __construct(protected(set) string $method, ?string $message = null)
    {
        $this->method = $method;
        parent::__construct($message ?? ($this->getName() . ": $this->method"));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Unknown method';
    }
}
