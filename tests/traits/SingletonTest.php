<?php

declare(strict_types=1);

use Php\Support\Exceptions\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class SingletonTest
 */
final class SingletonTest extends TestCase
{
    public function testBase(): void
    {
        $instance1 = SingletonParentClassTest::getInstance();
        static::assertInstanceOf(SingletonParentClassTest::class, $instance1);

        $instance2 = SingletonParentClassTest::getInstance();
        static::assertInstanceOf(SingletonParentClassTest::class, $instance2);

        static::assertEquals($instance1, $instance2);
    }

    public function testChild(): void
    {
        $parent = SingletonParentClassTest::getInstance();
        static::assertInstanceOf(SingletonParentClassTest::class, $parent);

        $child = SingletonChildClassTest::getInstance();
        static::assertInstanceOf(SingletonChildClassTest::class, $child);
        static::assertInstanceOf(SingletonParentClassTest::class, $child);

        static::assertNotEquals($child, $parent);
    }

    public function testPreventCreate(): void
    {
        $this->expectException(\Error::class);
        $parent = new SingletonParentClassTest();
    }

    public function testPreventClone(): void
    {
        $this->expectException(\Error::class);
        $instance  = SingletonParentClassTest::getInstance();
        $instance2 = clone $instance;
    }

    public function testPreventWakeup(): void
    {
        $this->expectException(Exception::class);
        $instance = SingletonChildClassTest::getInstance();

        $str = serialize($instance);

        unserialize($str);
    }
}

/**
 * Class SingletonParentClassTest
 */
class SingletonParentClassTest
{
    protected $username;

    use \Php\Support\Traits\Singleton;
}


/**
 * Class SingletonChildClassTest
 */
final class SingletonChildClassTest extends SingletonParentClassTest
{
    private $password;

    public function __sleep()
    {
        return [
            'username',
            'password',
        ];
    }
}
