<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class MetableTest
 */
final class MetableTest extends TestCase
{
    public function testBase(): void
    {
        $instance = new MetableClassTest;

        $meta = [
            'key' => 'val',
            '2key' => 'val2',
        ];
        $instance->withMeta($meta);

        static::assertEquals($instance->meta(), $meta);

        $meta = [
            'key' => 'val2',
            'array' => null,
            'key333' => '3',
        ];

        $instance->withMeta($meta);


        static::assertEquals($instance->meta(), [
            'key' => 'val2',
            'array' => null,
            '2key' => 'val2',
            'key333' => '3',
        ]);
    }

    public function testRecursive(): void
    {
        $instance = new MetableClassTest;

        $meta = [
            'key' => 'val',
            'array' => [
                'sk' => 1,
                'sk2' => 2,
            ],
            '2key' => 'val2',
        ];

        $instance->withMeta($meta);

        static::assertEquals($instance->meta(), $meta);

        $meta = [
            'key' => 'val2',
            'array' => null,
            'array_2' => [
                'sk' => 1,
            ],
            'key333' => '3',
        ];

        $instance->withMeta($meta);

        static::assertEquals($instance->meta(), [
            'key' => 'val2',
            'array' => null,
            '2key' => 'val2',
            'key333' => '3',
            'array_2' => [
                'sk' => 1,
            ],
        ]);

    }
}

/**
 * Class MetableClassTest
 */
class MetableClassTest
{
    use \Php\Support\Traits\Metable;
}
