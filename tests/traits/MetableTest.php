<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class MetableTest
 */
final class MetableTest extends TestCase
{
    public function testBase(): void
    {
        $instance = new MetableClassTest();

        $meta = [
            'key'  => 'val',
            '2key' => 'val2',
        ];
        $instance->withMeta($meta);

        static::assertEquals($instance->meta(), $meta);

        $meta = [
            'key'    => 'val2',
            'array'  => null,
            'key333' => '3',
        ];

        $instance->withMeta($meta);


        static::assertEquals(
            $instance->meta(),
            [
                'key'    => 'val2',
                'array'  => null,
                '2key'   => 'val2',
                'key333' => '3',
            ]
        );
    }

    public function testRecursive(): void
    {
        $instance = new MetableClassTest();

        $meta = [
            'key'   => 'val',
            'array' => [
                'sk'  => 1,
                'sk2' => 2,
            ],
            '2key'  => 'val2',
        ];

        $instance->withMeta($meta);

        static::assertEquals($instance->meta(), $meta);

        $meta = [
            'key'     => 'val2',
            'array'   => null,
            'array_2' => ['sk' => 1],
            'key333'  => '3',
        ];

        $instance->withMeta($meta);

        static::assertEquals(
            $instance->meta(),
            [
                'key'     => 'val2',
                'array'   => null,
                '2key'    => 'val2',
                'key333'  => '3',
                'array_2' => ['sk' => 1],
            ]
        );
    }

    public function testSetMetaAttribute(): void
    {
        $instance = new MetableClassTest();

        $instance->setMetaAttribute('test', 123);
        static::assertEquals($instance->metaAttribute('test'), 123);

        $instance->setMetaAttribute('params.id', 1);
        $instance->setMetaAttribute('params.isBool', true);
        $instance->setMetaAttribute('params.string', 'test');
        static::assertEquals(1, $instance->metaAttribute('params.id'));
        static::assertEquals(true, $instance->metaAttribute('params.isBool'));
        static::assertEquals('test', $instance->metaAttribute('params.string'));

        static::assertEquals(
            [
                'id'     => 1,
                'isBool' => true,
                'string' => 'test',
            ],
            $instance->metaAttribute('params')
        );

        static::assertEquals(
            [
                'test'   => 123,
                'params' =>
                    [
                        'id'     => 1,
                        'isBool' => true,
                        'string' => 'test',
                    ],
            ],
            $instance->meta()
        );
    }
}

/**
 * Class MetableClassTest
 */
class MetableClassTest
{
    use \Php\Support\Traits\Metable;
}
