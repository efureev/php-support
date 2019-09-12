<?php
declare(strict_types=1);

use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayStorageTest
 */
final class ArrayStorageTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $config = new ArrayStorageClassTest;

        $config->name = 'test';
        static::assertEquals('test', $config->name);

        $config->test = 'test in test';
        static::assertEquals('test in test', $config->test);

        $config->data = 1;
        static::assertEquals(1, $config->data);

        $config->null = null;
        static::assertNull($config->null);
    }

    public function testDeepData(): void
    {
        $config = new ArrayStorageClassTest;
        $key = 'test.sub.key';

        $config->$key = 1;

        static::assertEquals(1, $config->$key);

        $config->{'upper.sad.as'} = 'test';

        static::assertEquals('test', $config->{'upper.sad.as'});
    }

    public function testUnset(): void
    {
        $config = new ArrayStorageClassTest;
        $key = 'test.sub.key';

        $config->$key = 1;

        static::assertEquals(1, $config->$key);

        unset($config->$key);

        $this->expectException(Notice::class);
        static::assertNull($config->$key);

        unset($config->{'tst.sdf'});

        $this->expectException(Notice::class);
        static::assertNull($config->$key);
    }

    public function testAbsent(): void
    {
        $config = new ArrayStorageClassTest;

        $this->expectException(Notice::class);
        static::assertNull($config->test);
    }

    public function testIsset(): void
    {
        $config = new ArrayStorageClassTest;

        static::assertFalse(isset($config->test));

        $config->test2 = 'test2';
        static::assertTrue(isset($config->test2));

        $config->null = null;
        static::assertTrue(isset($config->null));

        static::assertFalse(isset($config->{'nullable.one.1'}));
    }

}

/**
 * Class ArrayStorageClassTest
 */
class ArrayStorageClassTest
{
    use \Php\Support\Traits\ArrayStorage;
}
