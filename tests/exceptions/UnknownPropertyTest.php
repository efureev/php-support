<?php
declare(strict_types=1);

use Php\Support\Exceptions\UnknownPropertyException;
use PHPUnit\Framework\TestCase;

final class UnknownPropertyTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new UnknownPropertyException('Invalid Arg');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(UnknownPropertyException::class, $e);
            $this->assertSame('Invalid Arg', $e->getMessage());
        }

        try {
            throw new UnknownPropertyException(null, 'test');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(UnknownPropertyException::class, $e);
            $this->assertSame('Unknown property', $e->getName());
            $this->assertSame('Unknown property: "test"', $e->getMessage());
            $this->assertSame('test', $e->getProperty());
        }

        try {
            throw new UnknownPropertyException();
        } catch (\Throwable $e) {
            $this->assertInstanceOf(UnknownPropertyException::class, $e);
            $this->assertSame('Unknown property', $e->getName());
            $this->assertSame('Unknown property', $e->getMessage());
        }
    }
}
