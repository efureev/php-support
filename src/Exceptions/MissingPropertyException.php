<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class MissingPropertyException
 *
 * @package Php\Support\Exceptions
 */
class MissingPropertyException extends ConfigException
{
    /**
     * MissingPropertyException constructor.
     *
     * @param null|string $message
     * @param null|string $property
     * @param ?array $config
     */
    public function __construct(?string $message = null, protected ?string $property = null, ?array $config = null)
    {
        parent::__construct(
            $message ?? ($this->getName() . ($this->property ? ': "' . $this->property . '"' : '')),
            $config
        );
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Missing property';
    }

    /**
     * @return null|string
     */
    public function getProperty(): ?string
    {
        return $this->property;
    }
}
