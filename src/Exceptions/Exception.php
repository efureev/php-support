<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use Php\Support\Traits\Maker;
use Php\Support\Traits\Thrower;
use Throwable;

/**
 * Class Exception
 *
 * @package Php\Support\Exceptions
 */
class Exception extends \Exception
{
    use Maker;
    use Thrower;

    /**
     * Exception constructor.
     *
     * @param ?string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message ?? $this->getName(), $code, $previous);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Exception';
    }
}
