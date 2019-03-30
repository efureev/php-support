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
