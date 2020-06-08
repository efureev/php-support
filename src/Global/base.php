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
