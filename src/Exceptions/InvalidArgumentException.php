<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use Throwable;

/**
 * Class InvalidArgumentException
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
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
