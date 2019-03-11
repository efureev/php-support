<?php
declare(strict_types=1);

use Php\Support\Types\Point;
use PHPUnit\Framework\TestCase;

/**
 * Class PointTest
 */
final class PointTest extends TestCase
{
    public function testCanBeInstance(): void
    {
        $this->assertInstanceOf(Point::class, new Point(1, 2));

        $listInvalid = [
            ['', 12],
            ['', ''],
            ['2', ''],
            ['s', 'sts'],
        ];
        foreach ($listInvalid as $item) {
            $p = new Point($item[0], $item[1]);
            $this->assertInstanceOf(Point::class, $p);
            $this->assertTrue($p->isEmpty());
        }
    }

    public function testGetAndSetFromDB(): void
    {
        $dbStrings = [
            '(54.94114600000000,63.57531800000000)',
            '(54.941146,63.575318)',
            //            '(54.941,63.575)',
        ];

        foreach ($dbStrings as $dbString) {
            $point = Point::fromDB($dbString);
            $this->assertInstanceOf(Point::class, $point);

            $this->assertEquals($point->longitude, 54.94114600000000);
            $this->assertEquals($point->latitude, 63.57531800000000);

            $this->assertIsFloat($point->latitude);
            $this->assertIsFloat($point->longitude);

//            $this->assertEquals($dbString, $point->toDB());
        }

    }

}
