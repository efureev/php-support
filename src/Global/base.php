<?php

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure || (is_object($value) && is_callable($value))
            ? $value()
            : $value;
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
