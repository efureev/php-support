<?php

declare(strict_types=1);

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
    public float $x = 0;

    public float $y = 0;

    /**
     * Point constructor.
     *
     * @param float $x
     * @param float $y
     */
    public function __construct(float $x, float $y)
    {
        $this->x = $x;
        $this->y = $y;
    }


    /**
     * @return string
     */
    /*public function __toString()
    {
        return $this->toDB();
    }*/

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            $this->x,
            $this->y,
        ];
    }

    /**
     * @param array $array
     *
     * @return Point|null
     */
    public static function fromArray(array $array): ?self
    {
        if (count($array) !== 2) {
            throw new InvalidParamException('Array must contains 2 elements: [ x, y ]');
        }

        return new static(...$array);
    }


    /**
     * @param int $options
     *
     * @return string|null
     * @throws \Php\Support\Exceptions\JsonException
     */
    public function toJson($options = 320): ?string
    {
        return Json::encode(
            [
                'x' => $this->x,
                'y' => $this->y,
            ],
            $options
        );
    }

    /**
     * @param string|null $string
     *
     * @return Jsonable|null
     * @throws \Php\Support\Exceptions\JsonException
     */
    public static function fromJson(?string $string): ?Jsonable
    {
        if (!$array = Json::decode($string)) {
            return null;
        }

        return new static($array['x'], $array['y']);
    }

    /**
     * @return string
     */
    public function toPgDB(): string
    {
        return '(' . $this->x . ',' . $this->y . ')';
    }

    /**
     * @param string|null $value
     *
     * @return $this|null
     */
    public function castFromDatabase(?string $value): ?self
    {
        if (empty($value)) {
            return null;
        }

        $string = mb_substr($value, 1, -1);

        if (empty($string)) {
            return null;
        }

        return new static(...explode(',', $string));
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
        return sqrt((($point1->x - $point2->x) ** 2) + (($point1->y - $point2->y) ** 2));
    }
}
