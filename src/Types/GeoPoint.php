<?php

declare(strict_types=1);

namespace Php\Support\Types;

use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Jsonable;

/**
 * Class GeoPoint
 *
 * @package Php\Support\Types
 *
 * @learn: x => longitude
 * @learn: y => latitude
 */
class GeoPoint extends Point
{

    /**
     * @param int $options
     *
     * @return string
     * @throws \Php\Support\Exceptions\JsonException
     */
    public function toJson($options = 320): string
    {
        return Json::encode(
            [
                'longitude' => $this->x,
                'latitude'  => $this->y,
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

        return new static($array['longitude'], $array['latitude']);
    }
}
