<?php

namespace Php\Support\Candidates\Traits;

use Php\Support\Candidates\Helpers\Str;
use Php\Support\Interfaces\Arrayable;

/**
 * Trait HasMutator
 *
 * @package Php\Support\Traits
 */
trait HasMutator
{
    /**
     * The cache of the mutated attributes for each class.
     *
     * @var array
     */
    protected static $mutatorCache = [];

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasGetMutator(string $key): bool
    {
        return method_exists($this, static::mutatorGetter($key));
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected static function mutatorGetter(string $key): string
    {
        return 'get' . static::getMutatorBaseName($key);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected static function mutatorSetter(string $key): string
    {
        return 'set' . static::getMutatorBaseName($key);
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected static function getMutatorBaseName(string $key): string
    {
        return Str::studly($key) . 'Attribute';
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function mutateAttribute(string $key, $value)
    {
        return $this->{static::mutatorGetter($key)}($value);
    }

    /**
     * Get the value of an attribute using its mutator for array conversion.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function mutateAttributeForArray(string $key, $value)
    {
        $value = $this->mutateAttribute($key, $value);

        return $value instanceof Arrayable ? $value->toArray() : $value;
    }


    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasSetMutator(string $key): bool
    {
        return method_exists($this, static::mutatorSetter($key));
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return mixed
     */
    protected function setMutatedAttributeValue(string $key, $value)
    {
        return $this->{static::mutatorSetter($key)}($value);
    }


    /**
     * Get the mutated attributes for a given instance.
     *
     * @return array
     */
    public function getMutatedAttributes(): array
    {
        $class = static::class;

        if (!isset(static::$mutatorCache[$class])) {
            static::cacheMutatedAttributes($class);
        }

        return static::$mutatorCache[$class];
    }

    /**
     * Extract and cache all the mutated attributes of a class.
     *
     * @param string $class
     *
     * @return void
     */
    public static function cacheMutatedAttributes(string $class): void
    {
        static::$mutatorCache[$class] = array_map(static function ($match) {
            return lcfirst(Str::snake($match));
        }, static::getMutatorMethods($class));
    }

    /**
     * Get all of the attribute mutator methods.
     *
     * @param mixed $class
     *
     * @return array
     */
    protected static function getMutatorMethods($class): array
    {
        preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches);

        return $matches[1];
    }
}
