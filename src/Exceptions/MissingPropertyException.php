<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class MissingPropertyException
 */
class MissingPropertyException extends ConfigException
{
    public function __construct(protected(set) ?string $property, ?string $message = null, array $config = [])
    {
        parent::__construct($message ?? ($this->getName() . ": '$this->property'"), $config);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Missing property';
    }
}
