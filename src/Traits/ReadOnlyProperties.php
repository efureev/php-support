<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use Php\Support\Exceptions\MissingPropertyException;

/**
 * Exposes chosen non-public properties for reading, and only those.
 *
 * Name them by overriding {@see self::readOnlyProperties()}:
 *
 * ```php
 * class User
 * {
 *     use ReadOnlyProperties;
 *
 *     protected string $name   = 'John';
 *     private string $password = 'secret';
 *
 *     protected function readOnlyProperties(): array
 *     {
 *         return ['name'];
 *     }
 * }
 *
 * $user->name;     // 'John'
 * $user->password; // MissingPropertyException
 * ```
 *
 * Until 6.0 the trait returned any declared property, private ones included, which made every
 * internal field publicly readable - the opposite of what the name promises. The allow-list is
 * empty by default, so a class that uses the trait without overriding the method exposes nothing.
 */
trait ReadOnlyProperties
{
    /**
     * Names of the properties this object exposes for reading.
     *
     * @return string[]
     */
    protected function readOnlyProperties(): array
    {
        return [];
    }

    public function __get(string $key): mixed
    {
        if ($this->isReadOnlyProperty($key)) {
            return $this->$key;
        }

        throw new MissingPropertyException($key);
    }

    public function __isset(string $key): bool
    {
        return $this->isReadOnlyProperty($key) && isset($this->$key);
    }

    private function isReadOnlyProperty(string $key): bool
    {
        return in_array($key, $this->readOnlyProperties(), true) && property_exists($this, $key);
    }
}
