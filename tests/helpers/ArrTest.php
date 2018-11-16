<?php
declare(strict_types=1);

use Php\Support\Helpers\Arr;
use PHPUnit\Framework\TestCase;

final class ArrTest extends TestCase
{

    public function testCanApplyCls()
    {
        $type = \Php\Support\Components\Params::class;
        $array = [
            ['key11' => 'value11', 'key12' => 'value12', 'key13' => 'value13'],
            ['key21' => 'value21', 'key22' => 'value22', 'key23' => 'value23'],
        ];

        $result = Arr::applyCls($array, $type);

        /**
         * @var int                            $key
         * @var \Php\Support\Components\Params $element
         */
        foreach ($result as $key => $element) {
            $this->assertInstanceOf($type, $element);

            $this->assertEquals($array[ $key ], $element->toArray());
        }

        $result = Arr::applyCls($array, $type, function ($cls, $data) {
            return (new $cls)->fromArray($data);
        });

        /**
         * @var int                            $key
         * @var \Php\Support\Components\Params $element
         */
        foreach ($result as $key => $element) {
            $this->assertInstanceOf($type, $element);

            $this->assertEquals($array[ $key ], $element->toArray());
        }

    }


    public function testMerge()
    {
        $array1 = [

            'key11' => 'value11',
            'key12' => [
                'value121',
                'value122'
            ],
            'key13' => [
                'key11' => 'value',
                'key12' => [
                    'value121', 'value122'
                ],
                'key13' => 'value13'
            ],

        ];

        $array2 = [

            'key11' => 'replace_value11',
            'key12' => [
                'replace_value121',
                'replace_value122'
            ],
            'key13' => [
                'key11' => 'replace_value',
                'key12' => [
                    'replace_value121', 'replace_value122'
                ],
                'key13' => 'replace_value13'
            ],
        ];

        $exceptReplace = [
            'key11' => 'replace_value11',
            'key12' => [
                'replace_value121',
                'replace_value122'
            ],
            'key13' => [
                'key11' => 'replace_value',
                'key12' => [
                    'replace_value121', 'replace_value122'
                ],
                'key13' => 'replace_value13'
            ],
        ];

        $exceptAdd = [
            'key11' => 'replace_value11',
            'key12' => [
                'value121',
                'value122',
                'replace_value121',
                'replace_value122'
            ],
            'key13' => [
                'key11' => 'replace_value',
                'key12' => [
                    'value121', 'value122', 'replace_value121', 'replace_value122'
                ],
                'key13' => 'replace_value13'
            ],
        ];

        $result = Arr::merge($array1, $array2);

        $this->assertArraySubset($exceptReplace, $result);
        $this->assertEquals($exceptReplace, $result);

        $result_add = Arr::merge($array1, $array2, false);
        $this->assertArraySubset($exceptAdd, $result_add);
        $this->assertEquals($exceptAdd, $result_add);
    }
}
