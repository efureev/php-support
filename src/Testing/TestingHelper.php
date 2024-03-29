<?php

declare(strict_types=1);

namespace Php\Support\Testing;

use ReflectionClass;

trait TestingHelper
{
    /**
     * Allows invoke protected|private methods
     *
     * @param string|object $class
     * @param string $method
     * @param ...$params
     *
     * @return mixed
     * @throws \ReflectionException
     *
     * @example
     *  $result = static::runProtectedMethod($sp, "registerService", T::class);
     */
    protected static function runProtectedMethod(string|object $class, string $method, ...$params): mixed
    {
        $methodReflex = new \ReflectionMethod($class, $method);
        $methodReflex->setAccessible(true);
        return $methodReflex->invoke($class, ...$params);
    }

    /**
     * Get a instance property (public/private/protected) value.
     *
     * @param object|string $object
     * @param string $propertyName
     *
     * @return mixed
     * @throws \ReflectionException
     *
     */
    protected static function getProperty(object|string $object, string $propertyName): mixed
    {
        $reflection = new ReflectionClass($object);

        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }
}
