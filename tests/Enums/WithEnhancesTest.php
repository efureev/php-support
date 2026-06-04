<?php

declare(strict_types=1);

namespace Php\Support\Tests\Enums;

use Php\Support\Tests\Enums\data\IntEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * trait WithEnhances
 */
final class WithEnhancesTest extends TestCase
{
    #[Test]
    public function values(): void
    {
        self::assertSame([1, 2, 3], IntEnum::values());
    }

    #[Test]
    public function names(): void
    {
        self::assertSame(['ONE', 'TWO', 'THREE'], IntEnum::names());
    }

    #[Test]
    public function hasName(): void
    {
        self::assertTrue(IntEnum::hasName('ONE'));
        self::assertTrue(IntEnum::hasName('TWO'));
        self::assertTrue(IntEnum::hasName('THREE'));

        self::assertFalse(IntEnum::hasName('one'));
        self::assertFalse(IntEnum::hasName('FOUR'));
        self::assertFalse(IntEnum::hasName('1'));
    }

    #[Test]
    public function casesToStringWithDecorator(): void
    {
        self::assertSame(
            'ONE=1, TWO=2, THREE=3',
            IntEnum::casesToString(static fn (IntEnum $item) => "{$item->name}={$item->value}")
        );
    }

    #[Test]
    public function casesToStringWithCustomDelimiter(): void
    {
        self::assertSame(
            '1|2|3',
            IntEnum::casesToString(static fn (IntEnum $item) => (string)$item->value, '|')
        );
    }

    #[Test]
    public function toKeyValueArray(): void
    {
        self::assertSame(
            [
                'ONE'   => 1,
                'TWO'   => 2,
                'THREE' => 3,
            ],
            IntEnum::toKeyValueArray()
        );
    }

    #[Test]
    public function toValueKeyArray(): void
    {
        self::assertSame(
            [
                1 => 'ONE',
                2 => 'TWO',
                3 => 'THREE',
            ],
            IntEnum::toValueKeyArray()
        );
    }
}
