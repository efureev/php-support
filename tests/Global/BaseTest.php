<?php

declare(strict_types=1);

namespace Php\Support\Tests;

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
                function __invoke()
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
                ['val' => new \stdClass, 'res' => true,],
                ['val' => [1, 2], 'res' => true,],
                ['val' => [1], 'res' => true,],
                ['val' => [0], 'res' => true,],
                ['val' => 1, 'res' => true,],
                ['val' => 42, 'res' => true,],
                ['val' => -42, 'res' => true,],
                ['val' => 'true', 'res' => true,],
                ['val' => '1', 'res' => true,],
                ['val' => 'on', 'res' => true,],
                ['val' => 'On', 'res' => true,],
                ['val' => 'ON', 'res' => true,],
                ['val' => 'yes', 'res' => true,],
                ['val' => 'YES', 'res' => true,],
                ['val' => 'TRUE', 'res' => true,],


                ['val' => 'off', 'res' => false,],
                ['val' => 'Off', 'res' => false,],
                ['val' => 'OFF', 'res' => false,],
                ['val' => 'no', 'res' => false,],
                ['val' => 'ja', 'res' => false,],
                ['val' => 'nein', 'res' => false,],
                ['val' => 'нет', 'res' => false,],
                ['val' => 'да', 'res' => false,],
                ['val' => null, 'res' => false,],
                ['val' => 0, 'res' => false,],
                ['val' => 'false', 'res' => false,],
                ['val' => 'FALSE', 'res' => false,],
                ['val' => 'string', 'res' => false,],
                ['val' => 'bool', 'res' => false,],
                ['val' => '0.0', 'res' => false,],
                ['val' => '4.2', 'res' => false,],
                ['val' => '0', 'res' => false,],
                ['val' => '', 'res' => false,],
                ['val' => '[]', 'res' => false,],
                ['val' => '{}', 'res' => false,],
                ['val' => 'false', 'res' => false,],
                ['val' => 'bar', 'res' => false,],

            ] as $data
        ) {
            $this->assertEquals(isTrue($data['val']), $data['res']);
        }
    }
}
