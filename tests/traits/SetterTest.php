<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SetterTest extends TestCase
{

    public function cls()
    {
        return new class()
        {
            use \Php\Support\Traits\Setter;

            protected $prop = 'secret';

            public function getKeyName()
            {
                return $this->prop;
            }

            public function setWriteOnly($val)
            {
                $this->prop = $val;
            }
        };
    }

    public function testInstance(): void
    {
        $cls = $this->cls();

        $this->assertEquals('secret', $cls->getKeyName());
        $cls->writeOnly = 'test';
        $this->assertEquals('test', $cls->getKeyName());

        $this->assertEquals('setJackDaniels', $cls::setter('jackDaniels'));
    }


    public function testInvalidCallException(): void
    {
        $cls = $this->cls();

        try {
            $cls->keyName = 'test';
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Php\Support\Exceptions\InvalidCallException::class, $e);
        }
    }

    public function testUnknownPropertyException(): void
    {
        $cls = $this->cls();

        try {
            $cls->valName = 'test';
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Php\Support\Exceptions\UnknownPropertyException::class, $e);
        }
    }

    public function testUnset(): void
    {
        $cls = $this->cls();

        unset($cls->writeOnly);

        try {
            unset($cls->keyName);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Php\Support\Exceptions\InvalidCallException::class, $e);
        }


    }

}
