<?php

declare(strict_types=1);

namespace Php\Support\Tests\Types;

use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Types\Point;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PointTest extends TestCase
{
    #[Test]
    public function constructDefaults(): void
    {
        $point = new Point();

        self::assertSame(0.0, $point->x);
        self::assertSame(0.0, $point->y);
    }

    #[Test]
    public function constructWithValues(): void
    {
        $point = new Point(1.5, -2.5);

        self::assertSame(1.5, $point->x);
        self::assertSame(-2.5, $point->y);
    }

    #[Test]
    public function toArray(): void
    {
        self::assertSame([1.5, 2.5], (new Point(1.5, 2.5))->toArray());
    }

    #[Test]
    public function fromArray(): void
    {
        $point = Point::fromArray([3.0, 4.0]);

        self::assertInstanceOf(Point::class, $point);
        self::assertSame(3.0, $point->x);
        self::assertSame(4.0, $point->y);
    }

    #[Test]
    public function fromArrayThrowsOnWrongSize(): void
    {
        $this->expectException(InvalidParamException::class);

        Point::fromArray([1.0]);
    }

    #[Test]
    public function fromArrayThrowsOnTooManyElements(): void
    {
        $this->expectException(InvalidParamException::class);

        Point::fromArray([1.0, 2.0, 3.0]);
    }

    #[Test]
    public function toJson(): void
    {
        self::assertSame('{"x":1.5,"y":2.5}', (new Point(1.5, 2.5))->toJson());
    }

    #[Test]
    public function fromJson(): void
    {
        $point = Point::fromJson('{"x":1.5,"y":2.5}');

        self::assertInstanceOf(Point::class, $point);
        self::assertSame(1.5, $point->x);
        self::assertSame(2.5, $point->y);
    }

    #[Test]
    public function fromJsonReturnsNullOnEmpty(): void
    {
        self::assertNull(Point::fromJson(null));
        self::assertNull(Point::fromJson(''));
    }

    #[Test]
    public function toPgDB(): void
    {
        self::assertSame('(1.5,2.5)', (new Point(1.5, 2.5))->toPgDB());
    }

    #[Test]
    public function castFromDatabase(): void
    {
        $point = Point::castFromDatabase('(1.5,2.5)');

        self::assertInstanceOf(Point::class, $point);
        self::assertSame(1.5, $point->x);
        self::assertSame(2.5, $point->y);
    }

    #[Test]
    public function castFromDatabaseReturnsNullOnEmpty(): void
    {
        self::assertNull(Point::castFromDatabase(null));
        self::assertNull(Point::castFromDatabase(''));
    }

    #[Test]
    public function calcDistance(): void
    {
        self::assertSame(5.0, Point::calcDistance(new Point(0, 0), new Point(3, 4)));
        self::assertSame(0.0, Point::calcDistance(new Point(1, 1), new Point(1, 1)));
    }
}
