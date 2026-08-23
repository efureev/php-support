<?php

declare(strict_types=1);

namespace Php\Support;

use Closure;
use Php\Support\Exceptions\MissingMethodException;
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Str;
use Php\Support\Structures\Collections\ReadableCollection;
use RuntimeException;

/**
 * The package's free functions, as static methods.
 *
 * `src/Global/base.php` declares the same helpers as global functions, each guarded by
 * `function_exists()`. That guard means a name already taken - by Laravel's `value()` or
 * `class_basename()`, for instance - silently wins, and the behaviour of code calling it changes
 * with autoload order. It also left the names in three styles at once: `class_basename`,
 * `dataGet`, `public_property_exists`.
 *
 * This class is the addressable entry point. Every method is camelCase, nothing can be shadowed,
 * and the global functions are thin wrappers over it, so both spellings run the same code.
 *
 * @see \Php\Support\Helpers\Arr for array helpers
 * @see \Php\Support\Helpers\Str for string helpers
 */
final class Func
{
    /**
     * Resolves a value: calls it when it is callable, returns it as-is otherwise.
     */
    public static function value(mixed $value, mixed ...$params): mixed
    {
        return $value instanceof Closure || (is_object($value) && is_callable($value))
            ? $value(...$params)
            : $value;
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * `*` fans out over an iterable, collecting the remaining path from every element.
     *
     * @param mixed $target
     * @param string|int|(string|int|null)[]|null $key
     * @param mixed $default
     */
    public static function dataGet(mixed $target, string|array|int|null $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', (string)$key);

        foreach ($key as $i => $segment) {
            unset($key[$i]);

            if ($segment === null) {
                return $target;
            }

            if ($segment === '*') {
                if ($target instanceof ReadableCollection) {
                    $target = $target->all();
                } elseif (!is_iterable($target)) {
                    return self::value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = self::dataGet($item, $key);
                }

                return in_array('*', $key, true) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return self::value($default);
            }
        }

        return $target;
    }

    /**
     * Applies $fn to every element, keeping the keys.
     *
     * @template TKey of array-key
     * @template TValue
     * @param callable $fn
     * @param iterable<TKey, TValue> $collection
     * @param mixed ...$args
     * @return array<TKey, mixed>
     */
    public static function mapValue(callable $fn, iterable $collection, mixed ...$args): array
    {
        $result = [];

        foreach ($collection as $key => $value) {
            $result[$key] = $fn($value, $key, ...$args);
        }

        return $result;
    }

    /**
     * Applies $fn to every element for its side effects.
     *
     * @template TKey of array-key
     * @template TValue
     * @param callable $fn
     * @param iterable<TKey, TValue> $collection
     * @param mixed ...$args
     */
    public static function eachValue(callable $fn, iterable $collection, mixed ...$args): void
    {
        foreach ($collection as $key => $value) {
            $fn($value, $key, ...$args);
        }
    }

    /**
     * Returns $value when $condition is truthy, $default otherwise. Both may be callables.
     */
    public static function when(mixed $condition, mixed $value, mixed $default = null): mixed
    {
        if ($result = self::value($condition)) {
            return $value instanceof Closure ? $value($result) : $value;
        }

        return self::value($default);
    }

    /**
     * The namespace of a class, without the class name itself.
     */
    public static function classNamespace(object|string $class): string
    {
        if (is_object($class)) {
            $class = $class::class;
        }

        return implode('\\', array_slice(explode('\\', $class), 0, -1));
    }

    /**
     * Interprets a value as a boolean, understanding "true"/"yes"/"on"/"1" and their opposites.
     *
     * @param bool $returnNull Return null instead of false when the value cannot be interpreted
     */
    public static function isTrue(mixed $val, bool $returnNull = false): ?bool
    {
        if ($val === null && $returnNull) {
            return null;
        }

        $boolVal = is_string($val)
            ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : (bool)$val;

        return $boolVal === null && !$returnNull ? false : $boolVal;
    }

    /**
     * Returns the object as-is, or instantiates the class name.
     *
     * @template T of object
     * @param T|class-string<T>|null $instance
     * @param mixed ...$params
     * @return T|null
     */
    public static function instance(string|object|null $instance, mixed ...$params): ?object
    {
        if (is_object($instance)) {
            return $instance;
        }

        if (is_string($instance) && class_exists($instance)) {
            return new $instance(...$params);
        }

        return null;
    }

    /**
     * The class name without its namespace.
     */
    public static function classBasename(string|object $class): string
    {
        $class = is_object($class) ? $class::class : $class;

        return basename(str_replace('\\', '/', $class));
    }

    /**
     * All traits used by a trait and by its own traits.
     *
     * @return array<string, string>
     */
    public static function traitUsesRecursive(string $trait): array
    {
        $traits = class_uses($trait);

        if (!$traits) {
            return [];
        }

        foreach ($traits as $trt) {
            $traits += self::traitUsesRecursive($trt);
        }

        return $traits;
    }

    /**
     * Whether a class uses a trait, directly or through another trait.
     */
    public static function doesTraitUse(string $class, string $trait): bool
    {
        return isset(self::traitUsesRecursive($class)[$trait]);
    }

    /**
     * All traits used by a class, its parents and their traits.
     *
     * @return array<string, string>
     */
    public static function classUsesRecursive(object|string $class): array
    {
        if (is_object($class)) {
            $class = $class::class;
        }

        $results = [];

        foreach (array_reverse((array)class_parents($class)) + [$class => $class] as $cls) {
            $results += self::traitUsesRecursive((string)$cls);
        }

        return array_unique($results);
    }

    /**
     * Calls a static method when it exists, returns null otherwise.
     */
    public static function remoteStaticCall(object|string|null $class, string $method, mixed ...$params): mixed
    {
        if (!$class) {
            return null;
        }

        if ((is_object($class) || class_exists($class)) && method_exists($class, $method)) {
            return $class::$method(...$params);
        }

        return null;
    }

    /**
     * Calls a static method or throws.
     *
     * @throws RuntimeException when no target is given
     * @throws MissingMethodException when the target has no such method
     */
    public static function remoteStaticCallOrThrow(object|string|null $class, string $method, mixed ...$params): mixed
    {
        if (!$class) {
            throw new RuntimeException('Target Class is absent');
        }

        if ((is_object($class) || class_exists($class)) && method_exists($class, $method)) {
            return $class::$method(...$params);
        }

        $strClass = is_object($class) ? $class::class : $class;

        throw new MissingMethodException("$strClass::$method");
    }

    /**
     * Calls an instance method when it exists, returns null otherwise.
     */
    public static function remoteCall(?object $class, string $method, mixed ...$params): mixed
    {
        if (!$class) {
            return null;
        }

        if (method_exists($class, $method)) {
            return $class->$method(...$params);
        }

        return null;
    }

    /**
     * The conventional getter name for an attribute.
     */
    public static function attributeToGetterMethod(string $attribute): string
    {
        return 'get' . ucfirst($attribute);
    }

    /**
     * The conventional setter name for an attribute.
     */
    public static function attributeToSetterMethod(string $attribute): string
    {
        return 'set' . ucfirst($attribute);
    }

    /**
     * The getter for an attribute, when the object has one.
     */
    public static function findGetterMethod(object $instance, string $attribute): ?string
    {
        $method = self::attributeToGetterMethod($attribute);

        return method_exists($instance, $method) ? $method : null;
    }

    /**
     * The setter for an attribute, when the object has one.
     */
    public static function findSetterMethod(object $instance, string $attribute): ?string
    {
        $method = self::attributeToSetterMethod($attribute);

        return method_exists($instance, $method) ? $method : null;
    }

    /**
     * The name of the public property matching an attribute, or null.
     */
    public static function publicPropertyExists(object $instance, string $attribute): ?string
    {
        $property = Str::toLowerCamel($attribute);

        return array_key_exists($property, get_object_vars($instance)) ? $property : null;
    }

    /**
     * The value of the public property matching an attribute, or null.
     */
    public static function getPropertyValue(object $instance, string $attribute): mixed
    {
        $property = self::publicPropertyExists($instance, $attribute);

        return $property === null ? null : $instance->$property;
    }
}
