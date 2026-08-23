<?php

/**
 * Global shortcuts for Php\Support\Func.
 *
 * Each declaration is guarded by function_exists(), so a name already taken by another package -
 * Laravel defines value(), class_basename() and class_uses_recursive(), among others - wins, and
 * this file quietly skips it. That makes the behaviour of a global call depend on autoload order.
 *
 * Call the facade directly when that matters. These wrappers hold no logic of their own.
 */

declare(strict_types=1);

use Php\Support\Func;

if (!function_exists('value')) {
    function value(mixed $value, mixed ...$params): mixed
    {
        return Func::value($value, ...$params);
    }
}

if (!function_exists('dataGet')) {
    /**
     * @param string|int|(string|int|null)[]|null $key
     */
    function dataGet(mixed $target, string|array|int|null $key, mixed $default = null): mixed
    {
        return Func::dataGet($target, $key, $default);
    }
}

if (!function_exists('mapValue')) {
    /**
     * @param iterable<array-key, mixed> $collection
     * @return array<array-key, mixed>
     */
    function mapValue(callable $fn, iterable $collection, mixed ...$args): array
    {
        return Func::mapValue($fn, $collection, ...$args);
    }
}

if (!function_exists('eachValue')) {
    /**
     * @param iterable<array-key, mixed> $collection
     */
    function eachValue(callable $fn, iterable $collection, mixed ...$args): void
    {
        Func::eachValue($fn, $collection, ...$args);
    }
}

if (!function_exists('when')) {
    function when(mixed $condition, mixed $value, mixed $default = null): mixed
    {
        return Func::when($condition, $value, $default);
    }
}

if (!function_exists('classNamespace')) {
    function classNamespace(object|string $class): string
    {
        return Func::classNamespace($class);
    }
}

if (!function_exists('isTrue')) {
    function isTrue(mixed $val, bool $returnNull = false): ?bool
    {
        return Func::isTrue($val, $returnNull);
    }
}

if (!function_exists('instance')) {
    /**
     * @template T of object
     * @param T|class-string<T>|null $instance
     * @return T|null
     */
    function instance(string|object|null $instance, mixed ...$params): ?object
    {
        return Func::instance($instance, ...$params);
    }
}

if (!function_exists('class_basename')) {
    function class_basename(string|object $class): string
    {
        return Func::classBasename($class);
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * @return array<string, string>
     */
    function trait_uses_recursive(string $trait): array
    {
        return Func::traitUsesRecursive($trait);
    }
}

if (!function_exists('does_trait_use')) {
    function does_trait_use(string $class, string $trait): bool
    {
        return Func::doesTraitUse($class, $trait);
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     * @return array<string, string>
     */
    function class_uses_recursive(object|string $class): array
    {
        return Func::classUsesRecursive($class);
    }
}

if (!function_exists('remoteStaticCall')) {
    function remoteStaticCall(object|string|null $class, string $method, mixed ...$params): mixed
    {
        return Func::remoteStaticCall($class, $method, ...$params);
    }
}

if (!function_exists('remoteStaticCallOrThrow')) {
    function remoteStaticCallOrThrow(object|string|null $class, string $method, mixed ...$params): mixed
    {
        return Func::remoteStaticCallOrThrow($class, $method, ...$params);
    }
}

if (!function_exists('remoteCall')) {
    function remoteCall(?object $class, string $method, mixed ...$params): mixed
    {
        return Func::remoteCall($class, $method, ...$params);
    }
}

if (!function_exists('attributeToGetterMethod')) {
    function attributeToGetterMethod(string $attribute): string
    {
        return Func::attributeToGetterMethod($attribute);
    }
}

if (!function_exists('attributeToSetterMethod')) {
    function attributeToSetterMethod(string $attribute): string
    {
        return Func::attributeToSetterMethod($attribute);
    }
}

if (!function_exists('findGetterMethod')) {
    function findGetterMethod(object $instance, string $attribute): ?string
    {
        return Func::findGetterMethod($instance, $attribute);
    }
}

if (!function_exists('findSetterMethodByProp')) {
    function findSetterMethodByProp(object $instance, string $attribute): ?string
    {
        return Func::findSetterMethod($instance, $attribute);
    }
}

if (!function_exists('public_property_exists')) {
    function public_property_exists(object $instance, string $attribute): ?string
    {
        return Func::publicPropertyExists($instance, $attribute);
    }
}

if (!function_exists('getPropertyValue')) {
    function getPropertyValue(object $instance, string $attribute): mixed
    {
        return Func::getPropertyValue($instance, $attribute);
    }
}
