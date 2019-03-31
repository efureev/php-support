<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class ConfigurableTest
 */
final class ConfigurableTest extends TestCase
{
    public function testConfigurable(): void
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
    }

    public function testConfigurableThrow(): void
    {
        $cls = new class()
        {
            use \Php\Support\Traits\ConfigurableTrait;
            public $prop;
        };

        try {
            $cls->configurable(['prop' => 'success', 'test' => 'fake']);
        } catch (\Throwable $exception) {
            $this->assertInstanceOf(Php\Support\Exceptions\InvalidParamException::class, $exception);
            $this->assertNull($exception->getParam());
        }

    }

}
