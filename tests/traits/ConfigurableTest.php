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
            public $_fn;

            public function setFn($val)
            {
                $this->_fn = $val;
            }
        };

        $this->assertNull($cls->prop);
        $cls->configurable(['prop' => 'success', 'test' => 'fake', 'fn' => 123], false);

        $this->assertEquals('success', $cls->prop);
        $this->assertEquals(123, $cls->_fn);
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
