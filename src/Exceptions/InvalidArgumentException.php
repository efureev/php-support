<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use Throwable;

/**
 * Class InvalidArgumentException
 *
 * @package Php\Support\Exceptions
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * Exception constructor.
     *
     * @param null|string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(?string $message = null, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message ?? $this->getName(), $code, $previous);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Invalid Argument';
    }
}
