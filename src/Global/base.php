<?php

use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Str;
use Php\Support\Structures\Collections\ReadableCollection;

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     */
    function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure || (is_object($value) && is_callable($value))
            ? $value(...$args)
            : $value;
    }
}

if (!function_exists('dataGet')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed $target
     * @param string|int|(string|int|null)[]|null $key
     * @param mixed $default
     * @return mixed
     */
    function dataGet(mixed $target, string|array|int|null $key, mixed $default = null): mixed
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
                    return value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = dataGet($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}


if (!function_exists('mapValue')) {
    /**
     * @template TKey of array-key
     * @template TValue
     * @param callable $fn
     * @param iterable<TKey, TValue> $collection
     * @param mixed ...$args
     * @return array<TKey, TValue>
     */
    function mapValue(callable $fn, iterable $collection, mixed ...$args): array
    {
        $result = [];

        foreach ($collection as $key => $value) {
            $result[$key] = $fn($value, $key, ...$args);
        }

        return $result;
    }
}

if (!function_exists('eachValue')) {
    /**
     * @template TKey of array-key
     * @template TValue
     * @param callable $fn
     * @param iterable<TKey, TValue> $collection
     * @param mixed ...$args
     * @return void
     */
    function eachValue(callable $fn, iterable $collection, mixed ...$args): void
    {
        foreach ($collection as $key => $value) {
            $fn($value, $key, ...$args);
        }
    }
}

if (!function_exists('when')) {
    /**
     * Returns a value when a condition is truthy.
     */
    function when(mixed $condition, mixed $value, mixed $default = null): mixed
    {
        if ($result = value($condition)) {
            return $value instanceof Closure ? $value($result) : $value;
        }

        return value($default);
    }
}

if (!function_exists('classNamespace')) {
    function classNamespace(object|string $class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return implode('\\', array_slice(explode("\\", $class), 0, -1));
    }
}

if (!function_exists('isTrue')) {
    /**
     * Returns bool value of a value
     */
    function isTrue(mixed $val, bool $return_null = false): ?bool
    {
        if ($val === null && $return_null) {
            return null;
        }

        $boolVal = (is_string($val)
            ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : (bool)$val);
        return ($boolVal === null && !$return_null ? false : $boolVal);
    }
}

if (!function_exists('instance')) {
    function instance(string|object|null $instance, mixed ...$params): ?object
    {
        if (is_object($instance)) {
            return $instance;
        }

        if (is_string($instance) && class_exists($instance)) {
            return new $instance(...$params);
        }

        return null;
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     */
    function class_basename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @return string[]
     */
    function trait_uses_recursive(string $trait): array
    {
        if (!$traits = class_uses($trait)) {
            return [];
        }

        foreach ($traits as $trt) {
            $traits += trait_uses_recursive($trt);
        }

        return $traits;
    }
}

if (!function_exists('does_trait_use')) {
    function does_trait_use(string $class, string $trait): bool
    {
        return isset(trait_uses_recursive($class)[$trait]);
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @return string[]
     */
    function class_uses_recursive(object|string $class): array
    {
        if (is_object($class)) {
            $class = $class::class;
        }

        $results = [];

        foreach (array_reverse((array)class_parents($class)) + [$class => $class] as $cls) {
            $results += trait_uses_recursive((string)$cls);
        }

        return array_unique($results);
    }
}


if (!function_exists('remoteStaticCall')) {
    /**
     * Returns result of an object's method if it exists in the object.
     */
    function remoteStaticCall(object|string|null $class, string $method, mixed ...$params): mixed
    {
        if (!$class) {
            return null;
        }

        if ((is_object($class) || class_exists($class)) && method_exists($class, $method)) {
            return $class::$method(...$params);
        }

        return null;
    }
}

if (!function_exists('remoteStaticCall')) {
    /**
     * Returns result of an object's method if it exists in the object or trow exception.
     */
    function remoteStaticCallOrTrow(object|string|null $class, string $method, mixed ...$params): mixed
    {
        if (!$class) {
            throw new RuntimeException('Target Class is absent');
        }

        if ((is_object($class) || class_exists($class)) && method_exists($class, $method)) {
            return $class::$method(...$params);
        }

        $strClass = is_object($class) ? $class::class : $class;
        throw new \Php\Support\Exceptions\MissingMethodException("$strClass::$method");
    }
}

if (!function_exists('remoteCall')) {
    /**
     * Returns result of an object's method if it exists in the object.
     */
    function remoteCall(?object $class, string $method, mixed ...$params): mixed
    {
        if (!$class) {
            return null;
        }
        if (method_exists($class, $method)) {
            return $class->$method(...$params);
        }

        return null;
    }
}

if (!function_exists('attributeToGetterMethod')) {
    /**
     * Returns getter-method's name or null by an attribute
     */
    function attributeToGetterMethod(string $attribute): string
    {
        return 'get' . ucfirst($attribute);
    }
}

if (!function_exists('attributeToSetterMethod')) {
    /**
     * Returns getter-method's name or null by an attribute
     */
    function attributeToSetterMethod(string $attribute): string
    {
        return 'set' . ucfirst($attribute);
    }
}

if (!function_exists('findGetterMethod')) {
    /**
     * Returns getter-method's name or null by an attribute
     */
    function findGetterMethod(object $instance, string $attribute): ?string
    {
        if (method_exists($instance, $method = attributeToGetterMethod($attribute))) {
            return $method;
        }

        return null;
    }
}

if (!function_exists('findSetterMethodByProp')) {
    /**
     * Returns getter-method's name or null by an attribute
     */
    function findSetterMethodByProp(object $instance, string $attribute): ?string
    {
        if (method_exists($instance, $method = attributeToSetterMethod($attribute))) {
            return $method;
        }

        return null;
    }
}

if (!function_exists('public_property_exists')) {
    /**
     * Returns existing public method (name) or null if missing
     */
    function public_property_exists(object $instance, string $attribute): ?string
    {
        $property = Str::toLowerCamel($attribute);
        $vars     = get_object_vars($instance);

        return array_key_exists($property, $vars) ? $property : null;
    }
}


if (!function_exists('getPropertyValue')) {
    /**
     * Returns a value from public property or null
     */
    function getPropertyValue(object $instance, string $attribute): mixed
    {
        $property = public_property_exists($instance, $attribute);
        if ($property) {
            return $instance->$property;
        }

        return null;
    }
}
