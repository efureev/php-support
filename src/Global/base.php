<?php

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @param mixed ...$args
     *
     * @return mixed
     */
    function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure || (is_object($value) && is_callable($value))
            ? $value(...$args)
            : $value;
    }
}

if (!function_exists('mapValue')) {
    function mapValue(callable $fn, iterable $collection, mixed ...$args): array
    {
        $result = [];

        foreach ($collection as $key => $value) {
            $result[$key] = $fn($value, $key, ...$args);
        }

        return $result;
    }
}

if (!function_exists('when')) {
    /**
     * Returns a value when a condition is truthy.
     *
     * @param mixed|bool|\Closure $condition
     * @param mixed|\Closure $value
     * @param mixed|\Closure|null $default
     *
     * @return mixed
     */
    function when($condition, $value, $default = null)
    {
        if ($result = value($condition)) {
            return $value instanceof Closure ? $value($result) : $value;
        }

        return value($default);
    }
}

if (!function_exists('classNamespace')) {
    /**
     * @param object|string $class
     *
     * @return string
     */
    function classNamespace($class): string
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
     *
     * @param mixed $val
     * @param bool $return_null
     *
     * @return bool|null
     */
    function isTrue($val, bool $return_null = false): ?bool
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
    /**
     * @param string|object $instance
     * @param mixed ...$params
     *
     * @return mixed
     */
    function instance($instance, ...$params): mixed
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
     *
     * @param string|object $class
     *
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits.
     *
     * @param string $trait
     *
     * @return array
     */
    function trait_uses_recursive(string $trait): array
    {
        if (!$traits = class_uses($trait)) {
            return [];
        }

        foreach ((array)$traits as $trt) {
            $traits += trait_uses_recursive($trt);
        }

        return $traits;
    }
}

if (!function_exists('does_trait_use')) {
    /**
     * @param string $class
     * @param string $trait
     *
     * @return bool
     */
    function does_trait_use(string $class, string $trait): bool
    {
        return isset(trait_uses_recursive($class)[$trait]);
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits.
     *
     * @param object|string $class
     *
     * @return array
     */
    function class_uses_recursive($class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
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
     *
     * @param string|object|null $class
     * @param string $method
     * @param mixed ...$params
     *
     * @return mixed
     */
    function remoteStaticCall(object|string|null $class, string $method, mixed ...$params): mixed
    {
        if (!$class) {
            return null;
        }

        if (
            (is_object($class) || (is_string($class) && class_exists($class))) &&
            method_exists($class, $method)
        ) {
            return $class::$method(...$params);
        }

        return null;
    }
}

if (!function_exists('remoteStaticCall')) {
    /**
     * Returns result of an object's method if it exists in the object or trow exception.
     *
     * @param string|object|null $class
     * @param string $method
     * @param mixed ...$params
     *
     * @return mixed
     */
    function remoteStaticCallOrTrow(object|string|null $class, string $method, mixed ...$params): mixed
    {
        if (!$class) {
            throw new RuntimeException('Target Class is absent');
        }

        if (
            (is_object($class) || (is_string($class) && class_exists($class))) &&
            method_exists($class, $method)
        ) {
            return $class::$method(...$params);
        }

        $strClass = is_object($class) ? $class::class : $class;
        throw new \Php\Support\Exceptions\MissingMethodException("$strClass::$method");
    }
}

if (!function_exists('remoteCall')) {
    /**
     * Returns result of an object's method if it exists in the object.
     *
     * @param object|null $class
     * @param string $method
     * @param mixed ...$params
     *
     * @return mixed
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
