<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Exceptions\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class SingletonTest
 */
final class SingletonTest extends TestCase
{
    public function testBase(): void
    {
        $instance1 = SingletonParentFixture::getInstance();
        static::assertInstanceOf(SingletonParentFixture::class, $instance1);

        $instance2 = SingletonParentFixture::getInstance();
        static::assertInstanceOf(SingletonParentFixture::class, $instance2);

        static::assertEquals($instance1, $instance2);
    }

    public function testChild(): void
    {
        $parent = SingletonParentFixture::getInstance();
        static::assertInstanceOf(SingletonParentFixture::class, $parent);

        $child = SingletonChildFixture::getInstance();
        static::assertInstanceOf(SingletonChildFixture::class, $child);
        static::assertInstanceOf(SingletonParentFixture::class, $child);

        static::assertNotEquals($child, $parent);
    }

    public function testPreventCreate(): void
    {
        $this->expectException(\Error::class);
        $parent = new SingletonParentFixture();
    }

    public function testPreventClone(): void
    {
        $this->expectException(\Error::class);
        $instance  = SingletonParentFixture::getInstance();
        $instance2 = clone $instance;
    }

    public function testPreventWakeup(): void
    {
        $this->expectException(Exception::class);
        $instance = SingletonChildFixture::getInstance();

        $str = serialize($instance);

        unserialize($str);
    }

    public function testCloneIsNotPublic(): void
    {
        // the constructor and __clone are both hidden, so the instance cannot be duplicated
        $clone = new \ReflectionMethod(SingletonParentFixture::class, '__clone');

        self::assertFalse($clone->isPublic(), '__clone must not be callable from outside');

        $clone->invoke(SingletonParentFixture::getInstance());

        self::assertSame(
            SingletonParentFixture::getInstance(),
            SingletonParentFixture::getInstance(),
            'the singleton is still the same instance'
        );
    }
}
