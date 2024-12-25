<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use UnexpectedValueException;

/**
 * Class InvalidValueException
 */
class InvalidValueException extends UnexpectedValueException
{
    public function getName(): string
    {
        return 'Invalid Return Value';
    }
}
