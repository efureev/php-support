<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class ArrayableTest
 */
final class ArrayableTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testArrayable(): void
    {
        $data_arrayable = $this->getMockBuilder(\Php\Support\Interfaces\Arrayable::class)->getMock();

        $data_arrayable
            ->method('toArray')
            ->willReturn(['key' => 'value']);

        $this->assertIsArray($data_arrayable->toArray());
        $this->assertEquals(['key' => 'value'], $data_arrayable->toArray());


        $actual = json_encode($data_arrayable->toArray());
        $this->assertSame('{"key":"value"}', $actual);

    }
}
