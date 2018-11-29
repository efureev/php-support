<?php

namespace Php\Support\Types;

use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Interfaces\Jsonable;

/**
 * Class Point
 *
 * @package Php\Support\Types
 */
class Point implements Jsonable, Arrayable
{
    /** @var float  Широта */
    public $latitude;

    /** @var float Долгота */
    public $longitude;

    /**
     * Point constructor.
     *
     * @param float|string $long
     * @param float|string $lat
     */
    public function __construct($long, $lat)
    {
        if (empty((float)$long) || empty((float)$lat)) {
            return;
        }

        $this->longitude = (float)$long;
        $this->latitude = (float)$lat;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toDB();
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function toArray(array $fields = [])
    {
        return [
            $this->longitude,
            $this->latitude,
        ];
    }

    /**
     * @param array $array
     *
     * @return Point|null
     */
    public static function fromArray(array $array)
    {
        if (count($array) !== 2) {
            throw new InvalidParamException('Должно быть два параметра');
        }

        if (empty($array[0]) || empty($array[1])) {
            return null;
        }

        return new static($array[0], $array[1]);
    }


    /**
     * @param int $options
     *
     * @return string|null
     */
    public function toJson($options = 320)
    {
        return Json::encode([
            'longitude' => $this->longitude,
            'latitude'  => $this->latitude,
        ], $options);
    }

    /**
     * @param string $string
     *
     * @return \Php\Support\Interfaces\Jsonable
     */
    public static function fromJson(string $string): ?Jsonable
    {
        if (!$array = Json::decode($string)) {
            return null;
        }

        return new static($array['longitude'], $array['latitude']);
    }

    /**
     * @param string $string
     *
     * @return \Php\Support\Interfaces\Jsonable|Point|null
     */
    public static function fromDB(string $string): ?Jsonable
    {
        if (empty($string)) {
            return null;
        }

        $string = mb_substr($string, 1, -1);

        if (empty($string)) {
            return null;
        }

        list($long, $lat) = explode(',', $string);

        return new static($long, $lat);
    }

    /**
     * @return string
     */
    public function toDB(): string
    {
        return '(' . $this->longitude . ',' . $this->latitude . ')';
    }


    /**
     * @return bool
     */
    public function isEmpty()
    {
        return !$this->longitude || !$this->latitude;
    }


    /**
     * Считает расстояние между точками
     *
     * @param Point $point1
     * @param Point $point2
     *
     * @return float
     */
    public static function calcDistance(Point $point1, Point $point2): float
    {
        return sqrt(pow($point1->latitude - $point2->latitude, 2) + pow($point1->longitude - $point2->longitude, 2));
    }

    /**
     * Возвращает первую точку из набора, которая строго ближе заданной дистанции
     *
     * @param array $points
     * @param float $distance
     *
     * @return Point|null
     */
    public function getNearPoint(array $points, float $distance): ?Point
    {
        foreach ($points as $point) {
            if (static::calcDistance($this, $point) < $distance) {
                return $point;
            }
        }

        return null;
    }
}
