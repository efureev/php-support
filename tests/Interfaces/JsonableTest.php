<?php

declare(strict_types=1);

namespace Php\Support\Tests;

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
        $data_jsonable = $this->createStub(\Php\Support\Interfaces\Jsonable::class);

        $data_jsonable
            ->method('toJson')
            ->willReturn('{"key":"value"}');

        $this->assertIsString($data_jsonable->toJson());
        $this->assertEquals(json_encode(['key' => 'value']), $data_jsonable->toJson());

        $null_jsonable = $this->createStub(\Php\Support\Interfaces\Jsonable::class);

        $null_jsonable
            ->method('toJson')
            ->willReturn(null);

        $this->assertNull($null_jsonable->toJson());
        $this->assertEquals(null, $null_jsonable->toJson());
    }
}
