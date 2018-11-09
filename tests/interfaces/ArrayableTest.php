<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ArrayableTest extends TestCase
{

    public function testArrayable(): void
    {
        $dataArrayable = $this->getMockBuilder(\Php\Support\Interfaces\Arrayable::class)->getMock();
        $dataArrayable->method('toArray')->willReturn(['key' => 'value']);

        $actual = \Php\Support\Helpers\Json::encode($dataArrayable);
        $this->assertSame('{"key":"value"}', $actual);

        $param = new \Php\Support\Components\BaseParams;
        $param->fromArray($dataArrayable->toArray());

        $this->assertSame('{"key":"value"}', $param->toJson());

    }


}
