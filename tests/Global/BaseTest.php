<?php

declare(strict_types=1);

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
            'cls'        => new class
            {
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
}
