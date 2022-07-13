<?php

declare(strict_types=1);

namespace Php\Support\Testing;

use function instance;

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
     *  $result = $this->runProtectedMethod($sp, "registerService", T::class);
     */
    public function runProtectedMethod(string|object $class, string $method, ...$params): mixed
    {
        $class = instance($class);

        $methodReflex = new \ReflectionMethod($class, $method);
        $methodReflex->setAccessible(true);
        return $methodReflex->invoke($class, ...$params);
    }
}
