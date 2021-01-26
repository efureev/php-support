<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait UseErrorsBox
 * @package Php\Support\Traits
 *
 * Use errors into your class
 */
trait UseErrorsBox
{
    private array $errors = [];

    public function setError($message): self
    {
        if ($message instanceof \Exception) {
            $message = $message->getMessage();
        }

        $this->errors[] = (string)$message;

        return $this;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function clearErrors(): self
    {
        $this->errors = [];

        return $this;
    }
}
