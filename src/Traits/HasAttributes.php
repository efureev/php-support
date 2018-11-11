<?php

namespace Php\Support\Traits;

use Php\Support\Helpers\Arr;

/**
 * Trait HasAttributes
 * Create class to base-model
 *
 * @package Php\Support\Traits
 */
trait HasAttributes
{
    use HasMutator;
    use Getter {
        Getter::__get as  __getParent;
        Getter::__isset as  __issetParent;
    }
    use Setter {
        Setter::__set as  __setParent;
        Setter::__unset as  __unsetParent;
    }

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The model attribute's original state.
     *
     * @var array
     */
    protected $original = [];

    /**
     * The changed model attributes.
     *
     * @var array
     */
    protected $changes = [];


    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if ($this->hasAttribute($key)) {
            return $this->getAttributeValue($key);
        }

        return null;
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @throws \Php\Support\Exceptions\UnknownPropertyException
     */
    public function __get(string $key)
    {
        if ($this->hasAttribute($key)) {
            return $this->getAttributeValue($key);
        }

        return static::__getParent($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset(string $key)
    {
        if ($this->hasAttribute($key)) {
            return isset($this->attributes[ $key ]);
        }

        return static::__issetParent($key);
    }

    /**
     * @param string $key
     */
    public function __unset(string $key)
    {
        if ($this->hasAttribute($key)) {
            unset($this->attributes[ $key ]);
        } else {
            static::__unsetParent($key);
        }
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string $key
     *
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        if (isset($this->attributes[ $key ])) {
            return $this->attributes[ $key ];
        }

        return null;
    }


    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function setAttribute(string $key, $value)
    {
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        $this->attributes[ $key ] = $value;

        return $this;
    }

    /**
     * Alias of setAttribute
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function addAttribute(string $key, $value)
    {
        $this->setAttribute($key, $value);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @throws \Php\Support\Exceptions\UnknownPropertyException
     */
    public function __set(string $key, $value)
    {
        if ($this->hasAttribute($key)) {
            $this->setAttribute($key, $value);
        } else {
            static::__setParent($key, $value);
        }
    }

    /**
     * Set the array of model attributes. No checking is done.
     *
     * @param  array $attributes
     * @param  bool  $sync
     *
     * @return $this
     */
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }

    /**
     * Get the model's original attribute values.
     *
     * @param  string|null $key
     * @param  mixed       $default
     *
     * @return mixed|array
     */
    public function getOriginal($key = null, $default = null)
    {
        return Arr::get($this->original, $key, $default);
    }

    /**
     * Get a subset of the model's attributes.
     *
     * @param  array|mixed $attributes
     *
     * @return array
     */
    public function only($attributes)
    {
        $results = [];

        foreach (is_array($attributes) ? $attributes : func_get_args() as $attribute) {
            $results[ $attribute ] = $this->getAttribute($attribute);
        }

        return $results;
    }

    /**
     * Sync the original attributes with the current.
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Sync a single original attribute with its current value.
     *
     * @param  string $attribute
     *
     * @return $this
     */
    public function syncOriginalAttribute($attribute)
    {
        $this->original[ $attribute ] = $this->attributes[ $attribute ];

        return $this;
    }

    /**
     * Sync the changed attributes.
     *
     * @return $this
     */
    public function syncChanges()
    {
        $this->changes = $this->getDirty();

        return $this;
    }

    /**
     * Determine if the model or given attribute(s) have been modified.
     *
     * @param  array|string|null $attributes
     *
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        return $this->hasChanges(
            $this->getDirty(), is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * Determine if the model or given attribute(s) have remained the same.
     *
     * @param  array|string|null $attributes
     *
     * @return bool
     */
    public function isClean($attributes = null)
    {
        return !$this->isDirty(...func_get_args());
    }

    /**
     * Determine if the model or given attribute(s) have been modified.
     *
     * @param  array|string|null $attributes
     *
     * @return bool
     */
    public function wasChanged($attributes = null)
    {
        return $this->hasChanges(
            $this->getChanges(), is_array($attributes) ? $attributes : func_get_args()
        );
    }

    /**
     * Determine if the given attributes were changed.
     *
     * @param  array             $changes
     * @param  array|string|null $attributes
     *
     * @return bool
     */
    protected function hasChanges($changes, $attributes = null)
    {
        if (empty($attributes)) {
            return count($changes) > 0;
        }

        foreach ((array)$attributes as $attribute) {
            if (array_key_exists($attribute, $changes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (!$this->originalIsEquivalent($key, $value)) {
                $dirty[ $key ] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Get the attributes that were changed.
     *
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Determine if the new and old values for a given key are equivalent.
     *
     * @param  string $key
     * @param  mixed  $current
     *
     * @return bool
     */
    protected function originalIsEquivalent($key, $current)
    {
        if (!array_key_exists($key, $this->original)) {
            return false;
        }

        $original = $this->getOriginal($key);

        if ($current === $original) {
            return true;
        } elseif (is_null($current)) {
            return false;
        }

        return is_numeric($current) && is_numeric($original)
            && strcmp((string)$current, (string)$original) === 0;
    }


}
