<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class ConfigurableTest
 */
final class ConfigurableTest extends TestCase
{
    public function testConfigurable(): void
    {
        $cls = new class () {
            use \Php\Support\Traits\ConfigurableTrait;

            public $prop;
            public $_fn;
            public $prop2;

            public function setFn($val)
            {
                $this->_fn = $val;
            }

            public function setProp2($val)
            {
                $this->prop2 = $val * 10;
            }
        };

        $this->assertNull($cls->prop);
        $cls->configurable(['prop' => 'success', 'test' => 'fake', 'fn' => 123, 'prop2' => 10], false);

        $this->assertEquals('success', $cls->prop);
        $this->assertEquals(123, $cls->_fn);
        $this->assertEquals(100, $cls->prop2);
        $this->assertFalse(property_exists($cls, 'test'));
    }

    public function testConfigurableThrow(): void
    {
        $cls = new class () {
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

    public function testConfigurable2(): void
    {
        $cls = new ConfigurableTraitTestClass();

        $this->assertNull($cls->prop);
        $cls->configurable(['prop' => 'success', 'test' => 'fake', 'fn' => 123], false);

        $this->assertEquals('success', $cls->prop);
        $this->assertEquals(123, $cls->_fn);
        $this->assertFalse(property_exists($cls, 'test'));
    }
}

class ConfigurableTraitTestClass
{
    use \Php\Support\Traits\ConfigurableTrait;

    public $prop;
    public $_fn;


    public function propertyExist(string $key): bool
    {
        return true;
    }

    public function setFn($val)
    {
        $this->_fn = $val;
    }
}
