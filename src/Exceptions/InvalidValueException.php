<?php
declare(strict_types=1);

namespace Php\Support\Exceptions;

use UnexpectedValueException;

/**
 * Class InvalidValueException
 * @package Php\Support\Exceptions
 */
class InvalidValueException extends UnexpectedValueException
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Invalid Return Value';
    }
}
