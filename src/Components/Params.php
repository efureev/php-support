<?php

namespace Php\Support\Components;

use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Jsonable;

class Params extends BaseParams
{

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
     * @var array
     */
    protected $dynamicHashKeys = [];

    /**
     * @param array $keys
     *
     * @return $this
     */
    public function setDynamicHashKeys(array $keys)
    {
        $this->dynamicHashKeys = $keys;

        return $this;
    }

    /**
     * @param mixed  $value
     * @param string $delimiter
     *
     * @return null|string
     */
    protected function dynamicId($value, string $delimiter = '|'): ?string
    {
        if ($this->dynamicHashKeys) {
            $values = array_filter(array_map(function ($key) use ($value) {
                return $value[ $key ] ?? null;
            }, $this->dynamicHashKeys));

            if ($values) {
                return md5(implode($delimiter, $values));
            }
        }

        return null;
    }

    /**
     * @var string|null
     */
    protected $uniqueKey;

    /**
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
     * @param mixed       $value
     * @param null|string $key
     *
     * @return null|string
     */
    public function add($value, ?string $key = null)
    {
        if (!$key) {
            $key = $this->uniqueKey ? $value[ $this->uniqueKey ] ?? null : $this->dynamicId($value);
        }

        $this->offsetSet($key, $value);

        if (!$key) {
            $key = count($this->_items) - 1;
        }

        return $key;
    }

}