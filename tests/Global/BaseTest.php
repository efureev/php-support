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
                ['val' => new \stdClass, 'res' => true, 'resNull' => true,],
                ['val' => [1, 2], 'res' => true, 'resNull' => true],
                ['val' => [1], 'res' => true, 'resNull' => true],
                ['val' => [0], 'res' => true, 'resNull' => true],
                ['val' => 1, 'res' => true, 'resNull' => true],
                ['val' => 42, 'res' => true, 'resNull' => true],
                ['val' => -42, 'res' => true, 'resNull' => true],
                ['val' => 'true', 'res' => true, 'resNull' => true],
                ['val' => '1', 'res' => true, 'resNull' => true],
                ['val' => 'on', 'res' => true, 'resNull' => true],
                ['val' => 'On', 'res' => true, 'resNull' => true],
                ['val' => 'ON', 'res' => true, 'resNull' => true],
                ['val' => 'yes', 'res' => true, 'resNull' => true],
                ['val' => 'YES', 'res' => true, 'resNull' => true],
                ['val' => 'TRUE', 'res' => true, 'resNull' => true],


                ['val' => 'off', 'res' => false, 'resNull' => false],
                ['val' => 'Off', 'res' => false, 'resNull' => false],
                ['val' => 'OFF', 'res' => false, 'resNull' => false],
                ['val' => 'no', 'res' => false, 'resNull' => false],
                ['val' => 'ja', 'res' => false, 'resNull' => false],
                ['val' => 'nein', 'res' => false, 'resNull' => false],
                ['val' => 'нет', 'res' => false, 'resNull' => false],
                ['val' => 'да', 'res' => false, 'resNull' => false],
                ['val' => null, 'res' => false, 'resNull' => null],
                ['val' => 0, 'res' => false, 'resNull' => false],
                ['val' => 'false', 'res' => false, 'resNull' => false],
                ['val' => 'FALSE', 'res' => false, 'resNull' => false],
                ['val' => 'string', 'res' => false, 'resNull' => false],
                ['val' => 'bool', 'res' => false, 'resNull' => false],
                ['val' => '0.0', 'res' => false, 'resNull' => false],
                ['val' => '4.2', 'res' => false, 'resNull' => false],
                ['val' => '0', 'res' => false, 'resNull' => false],
                ['val' => '', 'res' => false, 'resNull' => false],
                ['val' => '[]', 'res' => false, 'resNull' => false],
                ['val' => '{}', 'res' => false, 'resNull' => false],
                ['val' => 'false', 'res' => false, 'resNull' => false],
                ['val' => 'bar', 'res' => false, 'resNull' => false],

            ] as $data
        ) {
            //            $this->assertEquals(isTrue($data['val']), $data['res']);
            $this->assertEquals(isTrue($data['val'], true), $data['resNull']);
        }
    }
}
