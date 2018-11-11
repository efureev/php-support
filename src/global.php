<?php
/**
 * Created by PhpStorm.
 * User: efureev
 * Date: 12/11/2018
 * Time: 01:00
 */

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}