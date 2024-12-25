<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use LogicException;

class InvalidParamException extends LogicException
{
    public function __construct(?string $message = null, public private(set) readonly ?string $name = null)
    {
        parent::__construct(
            $message ?? sprintf('Invalid Parameter' . ($this->name ? ': %s' : ''), $this->name)
        );
    }

    public function getName(): string
    {
        return 'Invalid Parameter';
    }
}
