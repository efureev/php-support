<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class MakerTest
 */
final class MakerTest extends TestCase
{
    public function testBase(): void
    {
        static::assertInstanceOf(MakerClassTest::class, MakerClassTest::make());
        static::assertInstanceOf(MakerClassTest::class, MakerClassTest::make(1, 2));
    }

    public function testArg(): void
    {
        $instance = MakerArgClassTest::make();
        static::assertInstanceOf(MakerArgClassTest::class, $instance);
        static::assertNull($instance->public);

        $instance = MakerArgClassTest::make(23);
        static::assertInstanceOf(MakerArgClassTest::class, $instance);
        static::assertEquals(23, $instance->public);
    }
}

/**
 * Class MakerClassTest
 */
class MakerClassTest
{
    use \Php\Support\Traits\Maker;
}

/**
 * Class MakerArgClassTest
 */
class MakerArgClassTest
{
    use \Php\Support\Traits\Maker;

    public $public;

    /**
     * MakerArgClassTest constructor.
     *
     * @param int $a
     */
    public function __construct($a = null)
    {
        $this->public = $a;
    }
}
