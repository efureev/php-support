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
    public function providerSafeInt(): array
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
     * @param  int|string  $value
     * @param  int|string  $exp
     */
    public function testSafeInt(int|string $value, int|string $exp): void
    {
        self::assertTrue($exp === Number::safeInt($value));
    }
}
