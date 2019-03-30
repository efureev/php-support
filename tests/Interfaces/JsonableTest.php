<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class JsonableTest
 */
final class JsonableTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testToJson(): void
    {
        $data_jsonable = $this->getMockBuilder(\Php\Support\Interfaces\Jsonable::class)->getMock();

        $data_jsonable
            ->method('toJson')
            ->willReturn('{"key":"value"}');

        $this->assertIsString($data_jsonable->toJson());
        $this->assertEquals(json_encode(['key' => 'value']), $data_jsonable->toJson());

        $null_jsonable = $this->getMockBuilder(\Php\Support\Interfaces\Jsonable::class)->getMock();

        $null_jsonable
            ->method('toJson')
            ->willReturn(null);

        $this->assertNull($null_jsonable->toJson());
        $this->assertEquals(null, $null_jsonable->toJson());
    }
}
