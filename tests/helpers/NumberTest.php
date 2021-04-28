<?php

declare(strict_types=1);

namespace Php\Support\Tests;

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
            ['1', 1],
            [1, 1],
            [0, 0],
            [-1, -1],
            [9007199254740991, '9007199254740991'],
            [9007199254740992, '9007199254740992'],
            [9007199254740990, 9007199254740990],
            [-9007199254740991, '-9007199254740991'],
            [-9007199254740992, '-9007199254740992'],
            [-9007199254740990, -9007199254740990],
            [-9007199254740992, '-9007199254740992'],
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
