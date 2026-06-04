<?php

declare(strict_types=1);

namespace Php\Support\Tests\Types;

use Php\Support\Types\GeoPoint;
use Php\Support\Types\Point;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GeoPointTest extends TestCase
{
    #[Test]
    public function isPoint(): void
    {
        self::assertInstanceOf(Point::class, new GeoPoint());
    }

    #[Test]
    public function longitudeAndLatitude(): void
    {
        $point = new GeoPoint(37.6, 55.7);

        self::assertSame(37.6, $point->x);
        self::assertSame(55.7, $point->y);
    }

    #[Test]
    public function toJsonUsesGeoKeys(): void
    {
        self::assertSame(
            '{"longitude":37.6,"latitude":55.7}',
            (new GeoPoint(37.6, 55.7))->toJson()
        );
    }

    #[Test]
    public function fromJson(): void
    {
        $point = GeoPoint::fromJson('{"longitude":37.6,"latitude":55.7}');

        self::assertInstanceOf(GeoPoint::class, $point);
        self::assertSame(37.6, $point->x);
        self::assertSame(55.7, $point->y);
    }

    #[Test]
    public function fromJsonReturnsNullOnEmpty(): void
    {
        self::assertNull(GeoPoint::fromJson(null));
        self::assertNull(GeoPoint::fromJson(''));
    }

    #[Test]
    public function fromArrayReturnsGeoPoint(): void
    {
        $point = GeoPoint::fromArray([37.6, 55.7]);

        self::assertInstanceOf(GeoPoint::class, $point);
        self::assertSame(37.6, $point->x);
        self::assertSame(55.7, $point->y);
    }

    #[Test]
    public function toArrayIsInherited(): void
    {
        self::assertSame([37.6, 55.7], (new GeoPoint(37.6, 55.7))->toArray());
    }

    #[Test]
    public function calcDistance(): void
    {
        self::assertSame(5.0, GeoPoint::calcDistance(new GeoPoint(0, 0), new GeoPoint(3, 4)));
    }
}
