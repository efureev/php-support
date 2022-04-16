<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

trait HasReflection
{
    /**
     * @throws \ReflectionException
     */
    protected static function setMethodAccessible(object|string $cls, string $name): \ReflectionMethod
    {
        $class  = new \ReflectionClass($cls);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}
