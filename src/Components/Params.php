<?php

namespace Php\Support\Components;

use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Jsonable;
use Php\Support\Traits\DynamicHash;

class Params extends BaseParams
{
    use DynamicHash;

    /**
     * @param string $string
     *
     * @return \Php\Support\Interfaces\Jsonable
     */
    public function fromJsonString(string $string): Jsonable
    {
        return $this->fromArray(Json::decode($string));
    }


    /**
     * Alias of a offsetGet
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $this->offsetGet($key);
    }


    /**
     * Alias of a offsetSet
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * @var string|null
     */
    protected $uniqueKey;

    /**
     * Set unique key name for elements collection
     *
     * @param string $key
     *
     * @return $this
     */
    public function setUniqueKeyName(string $key)
    {
        $this->uniqueKey = $key;

        return $this;
    }

    /**
     * Add new element with key. If key is absent - create dynamic key: by unique index (uniqueKey) or by hash of group
     * fields (dynamicHashKeys)
     *
     * @param mixed       $value
     * @param null|string $key
     *
     * @return null|string
     */
    public function add($value, ?string $key = null)
    {
        if (!$key) {
            $key = $this->uniqueKey ? $value[ $this->uniqueKey ] ?? null : $this->dynamicHash($value);
        }

        $this->offsetSet($key, $value);

        if (!$key) {
            $key = count($this->_items) - 1;
        }

        return $key;
    }

}