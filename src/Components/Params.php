<?php

namespace Php\Support\Components;

use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Interfaces\Jsonable;
use Php\Support\Traits\Getter;

class Params implements
    \ArrayAccess,
    \IteratorAggregate,
    \JsonSerializable,
    \Countable,
    Arrayable,
    Jsonable
{
    use Getter {
        Getter::__get as  __getParent;
    }

    /** @var array */
    protected $_items = [];


    /**
     * Params constructor.
     *
     * @param array|null $array
     */
    public function __construct(?array $array = [])
    {
        $this->fromArray($array ?? []);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof \JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->_items);
    }

    /**
     * Get items as JSON
     *
     * @param int $options
     *
     * @return string|null
     */
    public function toJson($options = 320): ?string
    {
        return Json::encode($this->jsonSerialize(), $options);
    }

    /**
     * @param string $string
     *
     * @return \Php\Support\Interfaces\Jsonable|\Php\Support\Components\Params
     */
    public static function fromJson(string $string): Jsonable
    {
        return new static(Json::decode($string));
    }

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
     * Convert items to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson() ?? '';
    }


    /**
     * @param array $keys
     *
     * @return array
     */
    public function toArray(array $keys = []): array
    {
        $result = [];
        if ($keys) {
            foreach ($keys as $key) {
                if (isset($this->_items[ $key ])) {
                    $result[ $key ] = $this->_items[ $key ];
                }
            }
        } else {
            $result = $this->_items;
        }

        return Json::dataToArray($result);
    }

    /**
     * @param array $array
     *
     * @return \Php\Support\Components\Params
     */
    public function fromArray(array $array)
    {
        $this->_items = $array;

        return $this;
    }

    /**
     * Clear elements
     *
     * @return $this
     */
    public function clear()
    {
        $this->_items = [];

        return $this;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->_items);
    }

    /**
     * Checks if the given key or index exists in the array
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->_items);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->_items[ $offset ];
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
     * Offset to set
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_items[] = $value;
        } else {
            $this->_items[ $offset ] = $value;
        }
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

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        unset($this->_items[ $offset ]);
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_items);
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws \Php\Support\Exceptions\UnknownPropertyException
     */
    public function __get(string $name)
    {
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }

        return self::__getParent($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset(string $name)
    {
        if ($res = $this->offsetExists($name)) {
            return true;
        }

        $getter = static::getter($name);
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        }

        return false;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value)
    {
        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }

        if ($this->offsetExists($name)) {
            $this->offsetSet($name, $value);
        }
    }

    /**
     * @param string $name
     */
    public function __unset(string $name)
    {
        if ($this->offsetExists($name)) {
            $this->offsetUnset($name);

            return;
        }

        $setter = 'set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        }
    }

}