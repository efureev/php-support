<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ConfigurableTest extends TestCase
{

    public function testInstance(): void
    {
        $cls = new class()
        {
            use \Php\Support\Traits\ConfigurableTrait;
            public $prop;
        };

        $this->assertNull($cls->prop);
        $cls->configurable(['prop' => 'success', 'test' => 'fake'], false);

        $this->assertEquals('success', $cls->prop);
        $this->assertFalse(property_exists($cls, 'test'));

        try {
            $cls->configurable(['prop' => 'success', 'test' => 'fake']);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Php\Support\Exceptions\InvalidParamException::class, $e);
            $this->assertNull($e->getParam());
        }

    }

}
