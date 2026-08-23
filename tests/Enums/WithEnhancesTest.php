<?php

declare(strict_types=1);

namespace Php\Support\Tests\Enums;

use Php\Support\Exceptions\InvalidParamException;
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

    #[Test]
    public function hasValueWorksForIntBackedEnums(): void
    {
        // previously hasValue() lived only in WithEnhancesForStrings and was typed string,
        // so an int-backed enum had no way to ask this at all
        self::assertTrue(IntEnum::hasValue(1));
        self::assertTrue(IntEnum::hasValue(3));
        self::assertFalse(IntEnum::hasValue(999));
        self::assertFalse(IntEnum::hasValue('1'));
    }

    #[Test]
    public function tryFromName(): void
    {
        self::assertSame(IntEnum::ONE, IntEnum::tryFromName('ONE'));
        self::assertSame(IntEnum::THREE, IntEnum::tryFromName('THREE'));
        self::assertNull(IntEnum::tryFromName('NOPE'));
        self::assertNull(IntEnum::tryFromName('one'));
    }

    #[Test]
    public function fromName(): void
    {
        self::assertSame(IntEnum::TWO, IntEnum::fromName('TWO'));
    }

    #[Test]
    public function fromNameThrowsForAnUnknownName(): void
    {
        $this->expectException(InvalidParamException::class);
        IntEnum::fromName('NOPE');
    }

    #[Test]
    public function labels(): void
    {
        self::assertSame(
            [
                1 => 'ONE',
                2 => 'TWO',
                3 => 'THREE',
            ],
            IntEnum::labels()
        );
    }

    #[Test]
    public function randomReturnsACase(): void
    {
        self::assertContains(IntEnum::random(), IntEnum::cases());
    }
}
