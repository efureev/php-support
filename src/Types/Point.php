<?php

declare(strict_types=1);

namespace Php\Support\Types;

use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Interfaces\Jsonable;

/**
 * Class Point
 *
 * @package Php\Support\Types
 *
 * @implements Arrayable<int, float>
 *
 * @phpstan-consistent-constructor
 */
class Point implements Jsonable, Arrayable
{
    public function __construct(public float $x = 0, public float $y = 0)
    {
    }

    /**
     * @return array{float, float}
     */
    public function toArray(): array
    {
        return [
            $this->x,
            $this->y,
        ];
    }

    /**
     * @param array<int, float|int> $array Exactly two elements: [x, y]
     *
     * @return static
     * @throws InvalidParamException if the array does not contain exactly 2 elements
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
     * @return Jsonable|null `null` when the JSON does not describe a point
     */
    public static function fromJson(?string $string): ?Jsonable
    {
        $array = Json::decode($string);

        if (!is_array($array) || !isset($array['x'], $array['y'])) {
            return null;
        }

        if (!is_numeric($array['x']) || !is_numeric($array['y'])) {
            return null;
        }

        return new static((float)$array['x'], (float)$array['y']);
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
     * @return static|null
     */
    public static function castFromDatabase(?string $value): ?static
    {
        if (!$result = Arr::fromPostgresPoint($value)) {
            return null;
        }

        [
            $x,
            $y,
        ] = $result;
        return new static((float)$x, (float)$y);
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
