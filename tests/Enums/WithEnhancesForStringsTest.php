<?php

declare(strict_types=1);

namespace Php\Support\Tests\Enums;

use Php\Support\Tests\Enums\data\StringsEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * trait WithEnhancesForStrings
 */
final class WithEnhancesForStringsTest extends TestCase
{
    #[Test]
    public function casesToEscapeString(): void
    {
        self::assertEquals("'short', 'long', 'empty'", StringsEnum::casesToEscapeString());
    }

    #[Test]
    public function casesToString(): void
    {
        self::assertEquals('short, long, empty', StringsEnum::casesToString());
    }

    #[Test]
    public function values(): void
    {
        self::assertEquals(['short', 'long', 'empty'], StringsEnum::values());
    }

    #[Test]
    public function names(): void
    {
        self::assertEquals(['SHORT', 'LONG', 'EMPTY'], StringsEnum::names());
    }

    #[Test]
    public function hasValue(): void
    {
        self::assertFalse(StringsEnum::hasValue('---'));
        self::assertFalse(StringsEnum::hasValue('value'));
        self::assertFalse(StringsEnum::hasValue('EMPTY'));
        self::assertFalse(StringsEnum::hasValue('SHORT'));
        self::assertFalse(StringsEnum::hasValue('LONG'));

        self::assertTrue(StringsEnum::hasValue('long'));
        self::assertTrue(StringsEnum::hasValue('short'));
        self::assertTrue(StringsEnum::hasValue('empty'));
    }

    #[Test]
    public function hasName(): void
    {
        self::assertFalse(StringsEnum::hasName('---'));
        self::assertFalse(StringsEnum::hasName('value'));

        self::assertTrue(StringsEnum::hasName('EMPTY'));
        self::assertTrue(StringsEnum::hasName('SHORT'));
        self::assertTrue(StringsEnum::hasName('LONG'));

        self::assertFalse(StringsEnum::hasName('long'));
        self::assertFalse(StringsEnum::hasName('short'));
        self::assertFalse(StringsEnum::hasName('empty'));
    }
}
