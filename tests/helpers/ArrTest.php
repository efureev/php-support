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
}
