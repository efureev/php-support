<?php

declare(strict_types=1);

namespace Php\Support\Tests\enums;

use Php\Support\Tests\enums\data\StringsEnum;
use PHPUnit\Framework\TestCase;

/**
 * trait WithEnhancesForStrings
 */
final class WithEnhancesForStringsTest extends TestCase
{
    /**
     * @test
     */
    public function casesToEscapeString(): void
    {
        self::assertEquals("'short', 'long', 'empty'", StringsEnum::casesToEscapeString());
    }

    /**
     * @test
     */
    public function casesToString(): void
    {
        self::assertEquals('short, long, empty', StringsEnum::casesToString());
    }

    /**
     * @test
     */
    public function values(): void
    {
        self::assertEquals(['short', 'long', 'empty'], StringsEnum::values());
    }

    /**
     * @test
     */
    public function names(): void
    {
        self::assertEquals(['SHORT', 'LONG', 'EMPTY'], StringsEnum::names());
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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
