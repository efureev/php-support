<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use BadMethodCallException;
use Php\Support\Traits\Thrower;

class MissingMethodException extends BadMethodCallException
{
    use Thrower;

    public function __construct(protected ?string $method = null, string $message = null)
    {
        parent::__construct(
            $message ??
            ($this->getName() . ($this->method ? ': "' . $this->method . '"' : ''))
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Missing method';
    }

    /**
     * @return null|string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }
}
