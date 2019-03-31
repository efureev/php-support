<?php
declare(strict_types=1);

use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Jsonable;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrTest
 */
final class ArrTest extends TestCase
{
    public function testMerge(): void
    {
        $array1 = [

            'key11' => 'value11',
            'key12' => [
                'value121',
                'value122',
            ],
            'key13' => [
                'key11' => 'value',
                'key12' => [
                    'value121', 'value122',
                ],
                'key13' => 'value13',
            ],

        ];

        $array2 = [

            'key11' => 'replace_value11',
            'key12' => [
                'replace_value121',
                'replace_value122',
            ],
            'key13' => [
                'key11' => 'replace_value',
                'key12' => [
                    'replace_value121', 'replace_value122',
                ],
                'key13' => 'replace_value13',
            ],
        ];

        $except_replace = [
            'key11' => 'replace_value11',
            'key12' => [
                'replace_value121',
                'replace_value122',
            ],
            'key13' => [
                'key11' => 'replace_value',
                'key12' => [
                    'replace_value121', 'replace_value122',
                ],
                'key13' => 'replace_value13',
            ],
        ];

        $except_add = [
            'key11' => 'replace_value11',
            'key12' => [
                'value121',
                'value122',
                'replace_value121',
                'replace_value122',
            ],
            'key13' => [
                'key11' => 'replace_value',
                'key12' => [
                    'value121', 'value122', 'replace_value121', 'replace_value122',
                ],
                'key13' => 'replace_value13',
            ],
        ];

        $result = Arr::merge($array1, $array2);

        static::assertTrue(
            empty(array_diff_key($except_replace, $result)) && empty(array_diff_key($result, $except_replace))
        );
        $this->assertEquals($except_replace, $result);


        $result_add = Arr::merge($array1, $array2, false);

        static::assertTrue(
            empty(array_diff_key($except_add, $result_add)) && empty(array_diff_key($result_add, $except_add))
        );
        $this->assertEquals($except_add, $result_add);
    }


    public function providerDataToArray(): array
    {
        $arrayableClass = new class () implements \Php\Support\Interfaces\Arrayable
        {
            private $data = ['1', 2, 'test'];

            public function toArray(): array
            {
                return $this->data;
            }

        };
        $jsonableClass = new class () implements \Php\Support\Interfaces\Jsonable
        {
            private $data = ['32', 12, 'test'];

            public static function fromJson(string $json): ?Jsonable
            {
                return new self;
            }

            public function toJson($options = 320): ?string
            {
                return Json::encode($this->data, $options);
            }

        };

        return [
            [[1, 2, 3], [1, 2, 3]],
            [[], []],
            [[null], [null]],
            [
                [
                    'test' => 1,
                    0 => 14,
                    'nested' => [
                        'cl' => $arrayableClass,
                        'cl2' => $arrayableClass,
                        '1' => [1, 2, $jsonableClass],
                    ],
                    'csl' => $arrayableClass,
                ],
                [
                    'test' => 1,
                    0 => 14,
                    'nested' => [
                        'cl' => ['1', 2, 'test'],
                        'cl2' => ['1', 2, 'test'],
                        '1' => [1, 2, ['32', 12, 'test']],
                    ],
                    'csl' => ['1', 2, 'test'],
                ],
            ],
            [$arrayableClass, ['1', 2, 'test']],
            [$jsonableClass, ['32', 12, 'test']],
            [new class () implements \JsonSerializable
            {
                private $data = ['132', 12, 'test'];

                public function jsonSerialize()
                {
                    return $this->data;
                }

            }, ['132', 12, 'test']],
            [new ArrayObject([12, 'test 1']), [12, 'test 1']],
        ];
    }

    /**
     * @dataProvider providerDataToArray
     *
     * @param mixed $items
     * @param $exp
     *
     * @throws \Php\Support\Exceptions\JsonException
     */
    public function testDataToArray($items, $exp): void
    {
        $result = Arr::dataToArray($items);

        static::assertEquals($exp, $result);
    }

    /**
     * @return array
     */
    public function providerToArray(): array
    {
        $arrayableClass = new class () implements \Php\Support\Interfaces\Arrayable
        {
            private $data = ['1', 2, 'test'];

            public function toArray(): array
            {
                return $this->data;
            }

        };
        $jsonableClass = new class () implements \Php\Support\Interfaces\Jsonable
        {
            private $data = ['32', 12, 'test'];

            public static function fromJson(string $json): ?Jsonable
            {
                return new self;
            }

            public function toJson($options = 320): ?string
            {
                return Json::encode($this->data, $options);
            }

        };

        return [
            [[1, 2, 3], [1, 2, 3]],
            [[], []],
            [[null], [null]],
            [1, [1]],
            ['test', ['test']],
            [$arrayableClass, ['1', 2, 'test']],
            [$jsonableClass, ['32', 12, 'test']],
            [new class () implements \JsonSerializable
            {
                private $data = ['132', 12, 'test'];

                public function jsonSerialize()
                {
                    return $this->data;
                }

            }, ['132', 12, 'test']],
            [new ArrayObject([12, 'test 1']), [12, 'test 1']],
        ];
    }

    /**
     * @dataProvider providerToArray
     *
     * @param $items
     * @param $exp
     *
     * @throws \Php\Support\Exceptions\JsonException
     */
    public function testToArray($items, $exp): void
    {
        $result = Arr::toArray($items);

        static::assertEquals($exp, $result);
    }


    public function testAccessible(): void
    {
        static::assertTrue(Arr::accessible([]));
        static::assertTrue(Arr::accessible([1, 2]));
        static::assertTrue(Arr::accessible([1, []]));
        static::assertTrue(Arr::accessible(new ArrayObject([12, 'test 1'])));

        static::assertFalse(Arr::accessible(1));
        static::assertFalse(Arr::accessible('test'));
        static::assertFalse(Arr::accessible('0'));
        static::assertFalse(Arr::accessible(null));
        static::assertFalse(Arr::accessible(12.3));
        static::assertFalse(Arr::accessible(function () {
        }));
        static::assertFalse(Arr::accessible(new class()
        {
        }));
        static::assertFalse(Arr::accessible(new \stdClass));
    }


    public function testExists(): void
    {
        $array = [
            'key1' => 'val1',
            2 => 'val2',
            0 => 'val0',
            'test' => 'test',
        ];

        static::assertTrue(Arr::exists($array, 'key1'));
        static::assertTrue(Arr::exists($array, 2));
        static::assertTrue(Arr::exists($array, 0));
        static::assertTrue(Arr::exists($array, 'test'));
        static::assertFalse(Arr::exists($array, 'test.key1'));
        static::assertFalse(Arr::exists($array, 'te'));
        static::assertFalse(Arr::exists($array, ''));
        static::assertFalse(Arr::exists($array, null));
        static::assertFalse(Arr::exists($array, 1));


        $array = new ArrayObject($array);

        static::assertTrue(Arr::exists($array, 'key1'));
        static::assertTrue(Arr::exists($array, 2));
        static::assertTrue(Arr::exists($array, 0));
        static::assertTrue(Arr::exists($array, 'test'));
        static::assertFalse(Arr::exists($array, 'test.key1'));
        static::assertFalse(Arr::exists($array, 'te'));
        static::assertFalse(Arr::exists($array, ''));
        static::assertFalse(Arr::exists($array, 1));

    }


    public function testToIndexedArray(): void
    {
        $array = [
            'key1' => 'val1',
            'test' => 'test',
            'nested' => [
                'n1' => 'test1',
                'n2' => 'test2',
            ],
            'indexed1' => [
                1, 2, 3, 4,
            ],
            'indexed2' => [
                'val1', 'val2', 'val3', 'val4',
            ],
        ];

        $res = Arr::toIndexedArray($array);

        $expected = [
            'val1',
            'test',
            [
                'test1',
                'test2',
            ],
            [
                1, 2, 3, 4,
            ],
            [
                'val1', 'val2', 'val3', 'val4',
            ],
        ];

        static::assertEquals($expected, $res);
    }


    /**
     * @throws \Php\Support\Exceptions\JsonException
     */
    public function testToPostgresArray(): void
    {
        static::assertEquals('{val1,test,null,,null}', Arr::ToPostgresArray([
            'key1' => 'val1',
            'test' => 'test',
            'nested' => null,
            'indexed1' => '',
            'indexed2' => null,
        ]));
        static::assertEquals('{val1,test,indexed2}', Arr::ToPostgresArray([
            'val1',
            'test',
            'indexed2',
        ]));
        static::assertEquals('{1,12,32323}', Arr::ToPostgresArray([
            1,
            12,
            32323,
        ]));

        static::assertEquals('{1,null,0,,null}', Arr::ToPostgresArray([
            1,
            null,
            0,
            '',
            null,
        ]));
    }

    /**
     * @return array
     */
    public function providerRemoveByValue(): array
    {
        return [
            [[0 => 1, 2 => 3], 1, [1, 2, 3], 2],
            [[1 => 'val 21', 2 => 'vat', 3 => 'test'], 0, ['val 2', 'val 21', 'vat', 'test'], 'val 2'],
            [[1 => 'val 2', 2 => 'val 21', 3 => 'vat', 4 => 'test'], 0, ['val 2', 'val 2', 'val 21', 'vat', 'test'], 'val 2'],
            [['val 2', 'val 21', 'vat'], 3, ['val 2', 'val 21', 'vat', null], null],
            [['val 2', 'val 21', 'vat', null], null, ['val 2', 'val 21', 'vat', null], 1],
            [
                [
                    'key 1' => 'val 1',
                    'key 2' => 'val 2',
                ], 'key 4',
                [
                    'key 1' => 'val 1',
                    'key 2' => 'val 2',
                    'key 4' => 'val 4',
                ], 'val 4'],
            [['a'], null, ['a'], 'c'],
            [['a'], null, ['a'], null],
            [['a'], null, ['a'], ''],
            [['a'], null, ['a'], '1'],
            [['a'], null, ['a'], 1],
            [[2], null, [2], 1],
            [[], null, [], 'c'],
            [[], null, [], null],
        ];
    }

    /**
     * @dataProvider providerRemoveByValue
     *
     * @param $expArray
     * @param $expIdx
     * @param $array
     * @param $val
     */
    public function testRemoveByValue($expArray, $expIdx, $array, $val): void
    {
        $idx = Arr::removeByValue($array, $val);
        static::assertEquals($expArray, $array);
        static::assertEquals($expIdx, $idx);
    }

    /**
     * @return array
     */
    public function providerRemoveByValueAndReindex(): array
    {
        return [
            [[1, 3], 1, [1, 2, 3], 2],
            [['val 21', 'vat', 'test'], 0, ['val 2', 'val 21', 'vat', 'test'], 'val 2'],
            [['val 2', 'val 21', 'vat', 'test'], 0, ['val 2', 'val 2', 'val 21', 'vat', 'test'], 'val 2'],
            [['val 2', 'val 21', 'vat'], 3, ['val 2', 'val 21', 'vat', null], null],
            [['val 2', 'val 21', 'vat', null], null, ['val 2', 'val 21', 'vat', null], 1],
            [
                [
                    'val 1',
                    'val 2',
                ], 'key 4',
                [
                    'key 1' => 'val 1',
                    'key 2' => 'val 2',
                    'key 4' => 'val 4',
                ], 'val 4'],

            [['a'], null, ['a'], 'c'],
            [['a'], null, ['a'], null],
            [['a'], null, ['a'], ''],
            [['a'], null, ['a'], '1'],
            [['a'], null, ['a'], 1],
            [[2], null, [2], 1],
            [[], null, [], 'c'],
            [[], null, [], null],
        ];
    }

    /**
     * @dataProvider providerRemoveByValueAndReindex
     *
     * @param $expArray
     * @param $expIdx
     * @param $array
     * @param $val
     */
    public function testRemoveByValueAndReindex($expArray, $expIdx, $array, $val): void
    {
        $idx = Arr::removeByValue($array, $val, true);

        static::assertEquals($expArray, $array);
        static::assertEquals($expIdx, $idx);
    }
}
