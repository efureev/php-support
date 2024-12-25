<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class ArrayStorageTest
 */
final class ArrayStorageTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $config = new ArrayStorageClassTest();

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
        $config = new ArrayStorageClassTest();
        $key = 'test.sub.key';

        $config->$key = 1;

        static::assertEquals(1, $config->$key);

        $config->{'upper.sad.as'} = 'test';

        static::assertEquals('test', $config->{'upper.sad.as'});
    }

    public function testGetData(): void
    {
        $config = new ArrayStorageClassTest();

        $config->{'test.sub.key'} = 1;
        $config->{'test.sub.val'} = 'value';
        $config->{'test.next'} = 'next value';

        $expected = [
            'test' => [
                'sub' => [
                    'key' => 1,
                    'val' => 'value',
                ],
                'next' => 'next value',
            ],
        ];

        static::assertEquals($expected['test'], $config->test);
        static::assertEquals($expected, $config->getData());
    }

    public function testUnset(): void
    {
        $config = new ArrayStorageClassTest();

        $config->name = 'name';
        unset($config->name);
        static::assertTrue(isset($config->name));
        static::assertNull($config->name);

        $key = 'test.sub.key';
        $config->$key = 1;

        static::assertEquals(1, $config->$key);

        unset($config->$key);

        static::assertFalse(isset($config->$key));

        unset($config->{'tst.sdf'});

        static::assertFalse(isset($config->{'tst.sdf'}));
    }

    public function testAbsent(): void
    {
        $config = new ArrayStorageClassTest();

        static::assertNull($config->test);
    }

    public function testIsset(): void
    {
        $config = new ArrayStorageClassTest();

        static::assertFalse(isset($config->test));

        $config->test2 = 'test2';
        static::assertTrue(isset($config->test2));

        $config->null = null;
        static::assertTrue(isset($config->null));

        $config->name = 'name';
        static::assertTrue(isset($config->name));

        static::assertFalse(isset($config->{'nullable.one.1'}));
    }

    public function testValueExist(): void
    {
        $config = new ArrayStorageClassTest();

        $config->test = 'test2';

        static::assertTrue($config->valueExists('name'));
        static::assertTrue($config->valueExists('test'));
        static::assertFalse($config->valueExists('data'));
        static::assertFalse($config->valueExists('test2'));
        unset($config->test);

        static::assertFalse($config->valueExists('test'));
    }


    public function testOffsetExists(): void
    {
        $config = new ArrayStorageClassTest();
        $config->test2 = 'test2';

        static::assertTrue($config->offsetExists('test2'));
        static::assertTrue(isset($config['test2']));

        $config->null = null;
        static::assertTrue($config->offsetExists('null'));
        static::assertFalse($config->offsetExists('null2'));
    }

    public function testOffsetGet(): void
    {
        $config = new ArrayStorageClassTest();
        $config->test2 = 'test2';

        static::assertEquals('test2', $config->offsetGet('test2'));
        static::assertEquals('test2', $config['test2']);

        $config->null = null;
        static::assertNull($config['null']);
    }

    public function testOffsetSet(): void
    {
        $config = new ArrayStorageClassTest();
        $config['test2'] = 'val2';

        static::assertEquals('val2', $config->test2);
        static::assertEquals('val2', $config->offsetGet('test2'));
        static::assertEquals('val2', $config['test2']);

        $config['null'] = null;
        static::assertNull($config['null']);
    }

    public function testOffsetUnset(): void
    {
        $config = new ArrayStorageClassTest();
        $config['test2'] = 'val2';

        static::assertEquals('val2', $config->test2);
        static::assertEquals('val2', $config->offsetGet('test2'));
        static::assertEquals('val2', $config['test2']);

        unset($config['test2']);
    }
}

/**
 * Class ArrayStorageClassTest
 */
class ArrayStorageClassTest implements \ArrayAccess
{
    use \Php\Support\Traits\ArrayStorage;

    protected $name;
}
