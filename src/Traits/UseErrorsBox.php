<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Use errors into your class
 */
trait UseErrorsBox
{
    /** @var array<array-key, string> */
    private array $errors = [];

    /**
     * Append an error to the box.
     *
     * @param string|\Throwable $message A throwable contributes its message.
     * @param string|null $key Optional key; the same key overwrites the previous entry.
     */
    public function addError(string|\Throwable $message, ?string $key = null): static
    {
        if ($message instanceof \Throwable) {
            $message = $message->getMessage();
        }

        if ($key === null) {
            $this->errors[] = $message;
        } else {
            $this->errors[$key] = $message;
        }

        return $this;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<array-key, string>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * The first error, or null when the box is empty.
     */
    public function firstError(): ?string
    {
        foreach ($this->errors as $error) {
            return $error;
        }

        return null;
    }

    /**
     * The error stored under the given key, or null.
     */
    public function error(string $key): ?string
    {
        return $this->errors[$key] ?? null;
    }

    public function errorsCount(): int
    {
        return count($this->errors);
    }

    public function clearErrors(): static
    {
        $this->errors = [];

        return $this;
    }
}
