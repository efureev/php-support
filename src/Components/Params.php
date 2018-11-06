<?php

namespace Php\Support\Components;

use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Interfaces\Jsonable;

class Params implements
    \ArrayAccess,
    \JsonSerializable,
    \Countable,
    Arrayable,
    Jsonable
{
    /** @var array */
    private $_data = [];

    /**
     * Params constructor.
     *
     * @param array|null $array
     */
    public function __construct(array $array)
    {
        $this->fromArray($array);
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
        }, $this->_data);
    }

    /**
     * Get items as JSON
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 320): string
    {
        return Json::encode($this->jsonSerialize(), $options);
    }

    /**
     * @param string $str
     *
     * @return array
     */
    public function fromJson(string $str): array
    {
        return Json::decode($str);
    }

    /**
     * Convert items to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }


    /**
     * @param array $keys
     *
     * @return array
     */
    public function toArray(array $keys = []): array
    {
        $result = [];
        foreach ($keys as $key) {
            if (isset($this->_data[ $key ])) {
                $result[ $key ] = $this->_data[ $key ];
            }
        }

        return Json::dataToArray($result);
    }

    /**
     * @param array $array
     */
    public function fromArray(array $array): void
    {
        $this->_data = $array;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->_data);
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
        return array_key_exists($offset, $this->_data);
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
        return $this->_data[ $offset ];
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
            $this->_data[] = $value;
        } else {
            $this->_data[ $offset ] = $value;
        }
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[ $offset ]);
    }
}