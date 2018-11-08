<?php

namespace Php\Support\Components;

use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Jsonable;

class ParamsJson extends Params
{
    /** @var null|string */
    protected $_type;

    protected $_itemsRaw;


    public function __construct(?array $array = [])
    {
        parent::__construct($array);
        $this->normalizeElements();
    }

    /**
     * @param array $array
     *
     * @return \Php\Support\Components\ParamsJson
     */
    public function fromArray(array $array)
    {
        $this->_itemsRaw = $array;
        parent::fromArray($array);

        return $this->setElementsType($this->_type);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->_type
            ? [
                '_type' => $this->_type,
                '_data' => parent::jsonSerialize()
            ]
            : parent::jsonSerialize();
    }

    /**
     * @return $this
     */
    public function normalizeElements()
    {
        if ($this->_type) {
            $this->_items = Arr::applyCls($this->_itemsRaw, $this->_type);
        }

        return $this;
    }


    /**
     * Set class|type of elements
     *
     * @param string|null $type
     *
     * @return $this
     */
    public function setElementsType(?string $type)
    {
        $this->_type = $type;

        return $this->normalizeElements();
    }

    /**
     * @return null|string
     */
    public function getElementsType(): ?string
    {
        return $this->_type;
    }

    /**
     * @param string $string
     *
     * @return \Php\Support\Interfaces\Jsonable|\Php\Support\Components\ParamsJson
     */
    public static function fromJson(string $string): Jsonable
    {
        $array = Json::decode($string);

        $instance = new static();

        if (isset($array['_type'])) {
            $instance
                ->fromArray($array['_data'] ?? [])
                ->setElementsType($array['_type']);
        } else {
            $instance->fromArray($array ?? []);
        }

        return $instance;
    }
}