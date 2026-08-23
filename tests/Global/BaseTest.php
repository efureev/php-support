<?php

declare(strict_types=1);

namespace Php\Support\Tests\Global;

use Php\Support\Structures\Collections\ArrayCollection;
use Php\Support\Traits\Maker;
use Php\Support\Traits\Whener;
use Php\Support\Types\Point;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseTest
 */
final class BaseTest extends TestCase
{
    /**
     * @return array
     */
    private static function values(): array
    {
        return [
            'key'        => 'value 2',
            'int1'       => 2,
            'int2'       => -12,
            'array'      => [
                1,
                2,
                3,
                4,
                5,
            ],
            'string'     => 'string value',
            'null'       => null,
            'false'      => false,
            'true'       => true,
            'float'      => 12.31,
            'empty'      => '',
            'emptyArray' => [],
            'cls'        => new class {
                public function __invoke()
                {
                    return 'cls.test';
                }
            },
            'fn'         => static function () {
                return 'fn.test';
            },
        ];
    }

    public function testValue(): void
    {
        foreach (static::values() as $key => $val) {
            $result = value($val);

            if (is_callable($val)) {
                $this->assertEquals("$key.test", $result);
            } else {
                $this->assertEquals($val, $result);
            }
        }
    }

    public function testIsTrue(): void
    {
        foreach (
            [
                [
                    'val'     => new \stdClass(),
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => [
                        1,
                        2,
                    ],
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => [1],
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => [0],
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 1,
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 42,
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => -42,
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 'true',
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => '1',
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 'on',
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 'On',
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 'ON',
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 'yes',
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 'YES',
                    'res'     => true,
                    'resNull' => true,
                ],
                [
                    'val'     => 'TRUE',
                    'res'     => true,
                    'resNull' => true,
                ],


                [
                    'val'     => 'off',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'Off',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'OFF',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'no',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'ja',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'nein',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'нет',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'да',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => null,
                    'res'     => false,
                    'resNull' => null,
                ],
                [
                    'val'     => 0,
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'false',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'FALSE',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'string',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'bool',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => '0.0',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => '4.2',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => '0',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => '',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => '[]',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => '{}',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'false',
                    'res'     => false,
                    'resNull' => false,
                ],
                [
                    'val'     => 'bar',
                    'res'     => false,
                    'resNull' => false,
                ],

            ] as $data
        ) {
            //            $this->assertEquals(isTrue($data['val']), $data['res']);
            $this->assertEquals(isTrue($data['val'], true), $data['resNull']);
        }
    }


    public function testInstance(): void
    {
        $cls = instance(\stdClass::class);
        static::assertEquals(\stdClass::class, \get_class($cls));
        static::assertTrue(is_object($cls));

        $cls2 = instance($cls);
        static::assertEquals(\get_class($cls), \get_class($cls2));
        static::assertEquals($cls, $cls2);
        static::assertTrue(is_object($cls2));

        /** @var Point $point */
        $point = instance(Point::class, 1, 10);
        static::assertEquals(Point::class, \get_class($point));
        static::assertTrue(is_object($point));
        static::assertEquals(1, $point->x);
        static::assertEquals(10, $point->y);

        foreach (
            [
                null,
                '1',
                'true',
                'false',
                'null',
                '0',
            ] as $val
        ) {
            static::assertNull(instance($val));
        }
    }


    public function testTraitUsesRecursive(): void
    {
        $traits = trait_uses_recursive(TraitUsesRecursiveClass::class);

        static::assertEquals(
            [
                \Php\Support\Traits\Singleton::class              => \Php\Support\Traits\Singleton::class,
                \Php\Support\Traits\UseConfigurableStorage::class => \Php\Support\Traits\UseConfigurableStorage::class,
                \Php\Support\Traits\UseStorage::class             => \Php\Support\Traits\UseStorage::class,
                \Php\Support\Traits\ConfigurableTrait::class      => \Php\Support\Traits\ConfigurableTrait::class,
            ],
            $traits
        );
    }

    public function testClassUsesRecursive(): void
    {
        $traits = class_uses_recursive(RecursiveClass::class);

        static::assertEquals(
            [
                \Php\Support\Traits\Singleton::class              => \Php\Support\Traits\Singleton::class,
                \Php\Support\Traits\UseConfigurableStorage::class => \Php\Support\Traits\UseConfigurableStorage::class,
                \Php\Support\Traits\UseStorage::class             => \Php\Support\Traits\UseStorage::class,
                \Php\Support\Traits\ConfigurableTrait::class      => \Php\Support\Traits\ConfigurableTrait::class,
                \Php\Support\Traits\Maker::class                  => \Php\Support\Traits\Maker::class,
            ],
            $traits
        );
    }

    public function testClassBasename(): void
    {
        $name = class_basename(RecursiveClass::class);
        static::assertEquals('RecursiveClass', $name);
        $name = class_basename(new \stdClass());
        static::assertEquals('stdClass', $name);
    }

    #[Test]
    public function dataGetWalksArraysObjectsAndCollections(): void
    {
        $target = [
            'user' => [
                'name'  => 'a',
                'roles' => [
                    'admin',
                    'dev',
                ],
            ],
        ];

        self::assertSame('a', dataGet($target, 'user.name'));
        self::assertSame('admin', dataGet($target, 'user.roles.0'));
        self::assertSame($target, dataGet($target, null));
        self::assertSame('def', dataGet($target, 'user.missing', 'def'));
        self::assertSame('def', dataGet($target, 'nope.deeper', 'def'));

        $object       = new \stdClass();
        $object->name = 'obj';
        self::assertSame('obj', dataGet($object, 'name'));
        self::assertSame('def', dataGet($object, 'missing', 'def'));
    }

    #[Test]
    public function dataGetSupportsTheWildcard(): void
    {
        $target = [
            'users' => [
                ['name' => 'a'],
                ['name' => 'b'],
            ],
        ];

        self::assertSame(['a', 'b'], dataGet($target, 'users.*.name'));
        self::assertSame([null, null], dataGet($target, 'users.*.missing'));
        self::assertSame('def', dataGet('scalar', '*', 'def'));

        $collection = new ArrayCollection([['name' => 'a']]);
        self::assertSame(['a'], dataGet(['users' => $collection], 'users.*.name'));
    }

    #[Test]
    public function dataGetAcceptsAnArrayKey(): void
    {
        self::assertSame('a', dataGet(['x' => ['y' => 'a']], ['x', 'y']));
    }

    #[Test]
    public function whenHelper(): void
    {
        self::assertSame('yes', when(true, 'yes'));
        self::assertSame('no', when(false, 'yes', 'no'));
        self::assertNull(when(false, 'yes'));
        self::assertSame('got:1', when(1, static fn($result) => 'got:' . $result));
        self::assertSame('lazy', when(false, 'yes', static fn() => 'lazy'));
    }

    #[Test]
    public function doesTraitUseAndClassUsesRecursive(): void
    {
        self::assertTrue(does_trait_use(RemoteCallTraitHolder::class, Maker::class));
        self::assertFalse(does_trait_use(RemoteCallTraitHolder::class, Whener::class));

        $traits = class_uses_recursive(new RemoteCallTraitHolder());
        self::assertContains(Maker::class, $traits);
    }
}
