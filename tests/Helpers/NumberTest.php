<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Helpers\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
                9007199254740991,
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
                -9007199254740991,
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
                '9007199254740990',
                9007199254740990,
            ],
            [
                '9007199254740991',
                9007199254740991,
            ],
            [
                '9007199254740992',
                '9007199254740992',
            ],
            [
                '900719925474099123',
                '900719925474099123',
            ],
            [
                '-900719925474099123',
                '-900719925474099123',
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

    #[DataProvider('providerSafeInt')]
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

    #[DataProvider('providerIsInteger')]
    #[Test]
    public function isInteger(mixed $value, bool $exp): void
    {
        self::assertEquals($exp, Number::isInteger($value));
    }

    public function testSafeIntKeepsNonIntegerValuesAsStrings(): void
    {
        // '1.9' used to come back as int 1, silently dropping the fraction
        self::assertSame('1.9', Number::safeInt('1.9'));
        self::assertSame('-0.5', Number::safeInt('-0.5'));
        self::assertSame('1e3', Number::safeInt('1e3'));
        self::assertSame('  42  ', Number::safeInt('  42  '));
    }

    public function testFormat(): void
    {
        self::assertSame('1 234 567.89', Number::format(1234567.891, 2));
        self::assertSame('1 235', Number::format(1234.5));
        self::assertSame('1,234.50', Number::format(1234.5, 2, '.', ','));
        self::assertSame('0', Number::format(0));
    }

    public function testClamp(): void
    {
        self::assertSame(10, Number::clamp(15, 0, 10));
        self::assertSame(0, Number::clamp(-5, 0, 10));
        self::assertSame(5, Number::clamp(5, 0, 10));
        self::assertSame(1.5, Number::clamp(1.5, 0.0, 10.0));
    }

    public function testClampRejectsAnInvertedRange(): void
    {
        $this->expectException(InvalidParamException::class);
        Number::clamp(1, 10, 0);
    }

    public function testPercentage(): void
    {
        self::assertSame(12.5, Number::percentage(25, 200));
        self::assertSame(33.33, Number::percentage(1, 3));
        self::assertSame(33.333, Number::percentage(1, 3, 3));
        self::assertNull(Number::percentage(1, 0), 'division by zero returns null, not INF');
    }

    public function testHumanize(): void
    {
        // the integer part must survive: an early version returned "5 B" for 500
        self::assertSame('0 B', Number::humanize(0));
        self::assertSame('500 B', Number::humanize(500));
        self::assertSame('1023 B', Number::humanize(1023));
        self::assertSame('1 KB', Number::humanize(1024));
        self::assertSame('1.5 KB', Number::humanize(1536));
        self::assertSame('1 MB', Number::humanize(1048576));
        self::assertSame('1 GB', Number::humanize(1073741824));
    }

    public function testHumanizeRejectsNegativeSizes(): void
    {
        $this->expectException(InvalidParamException::class);
        Number::humanize(-1);
    }

    public function testOrdinal(): void
    {
        self::assertSame('1st', Number::ordinal(1));
        self::assertSame('2nd', Number::ordinal(2));
        self::assertSame('3rd', Number::ordinal(3));
        self::assertSame('4th', Number::ordinal(4));
        // the teens are the exception
        self::assertSame('11th', Number::ordinal(11));
        self::assertSame('12th', Number::ordinal(12));
        self::assertSame('13th', Number::ordinal(13));
        self::assertSame('21st', Number::ordinal(21));
        self::assertSame('111th', Number::ordinal(111));
        self::assertSame('-1st', Number::ordinal(-1));
        self::assertSame('0th', Number::ordinal(0));
    }
}
