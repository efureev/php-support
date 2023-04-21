<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

use Php\Support\Helpers\Number;
use PHPUnit\Framework\TestCase;

/**
 * Class NumberTest
 */
final class NumberTest extends TestCase
{
    public static function providerSafeInt(): array
    {
        return [
            [
                '1',
                1,
            ],
            [
                1,
                1,
            ],
            [
                0,
                0,
            ],
            [
                -1,
                -1,
            ],
            [
                9007199254740991,
                '9007199254740991',
            ],
            [
                9007199254740992,
                '9007199254740992',
            ],
            [
                9007199254740990,
                9007199254740990,
            ],
            [
                -9007199254740991,
                '-9007199254740991',
            ],
            [
                -9007199254740992,
                '-9007199254740992',
            ],
            [
                -9007199254740990,
                -9007199254740990,
            ],
            [
                -9007199254740992,
                '-9007199254740992',
            ],
            [
                '66ede6f7-1b11-4d01-8bbb-c6412b12eac3',
                '66ede6f7-1b11-4d01-8bbb-c6412b12eac3',
            ],
            [
                'test',
                'test',
            ],
        ];
    }

    /**
     * @dataProvider providerSafeInt
     *
     * @param int|string $value
     * @param int|string $exp
     */
    public function testSafeInt(int|string $value, int|string $exp): void
    {
        self::assertTrue($exp === Number::safeInt($value));
    }


    public static function providerIsInteger(): array
    {
        return [
            [
                '1',
                true,
            ],
            [
                1,
                true,
            ],
            [
                0,
                true,
            ],
            [
                -1,
                true,
            ],
            [
                9007199254740991,
                true,
            ],
            [
                9007199254740992,
                true,
            ],
            [
                9007199254740990,
                true,
            ],
            [
                -9007199254740991,
                true,
            ],
            [
                -9007199254740992,
                true,
            ],
            [
                -9007199254740990,
                true,
            ],
            [
                -9007199254740992,
                true,
            ],
            [
                '66ede6f7-1b11-4d01-8bbb-c6412b12eac3',
                false,
            ],
            [
                'test',
                false,
            ],

            [
                23,
                true,
            ],
            [
                '-23',
                true,
            ],
            [
                '23',
                true,
            ],
            [
                23.3,
                false,
            ],
            [
                '23.3',
                false,
            ],
            [
                '23.3',
                false,
            ],
            [
                '23,2',
                false,
            ],
            [
                'null',
                false,
            ],
            [
                null,
                false,
            ],
            [
                '',
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerIsInteger
     *
     * @param int|string $value
     * @param bool $exp
     * @test
     */
    public function isInteger(mixed $value, bool $exp): void
    {
        self::assertEquals($exp, Number::isInteger($value));
    }
}
