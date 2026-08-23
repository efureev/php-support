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
     * @return string|null
     */
    public function toJson($options = 320): ?string
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
     * @return Jsonable|null `null` when the JSON does not describe a geo point
     */
    public static function fromJson(?string $string): ?Jsonable
    {
        $array = Json::decode($string);

        if (!is_array($array) || !isset($array['longitude'], $array['latitude'])) {
            return null;
        }

        if (!is_numeric($array['longitude']) || !is_numeric($array['latitude'])) {
            return null;
        }

        return new static((float)$array['longitude'], (float)$array['latitude']);
    }
}
