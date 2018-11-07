<?php
declare(strict_types=1);

use Php\Support\Exceptions\InvalidParamException;
use PHPUnit\Framework\TestCase;

final class InvalidParamTest extends TestCase
{

    public function testThrow()
    {
        try {
            throw new InvalidParamException('Invalid Param');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(InvalidParamException::class, $e);
            $this->assertSame('Invalid Param', $e->getMessage());
        }

        try {
            throw new InvalidParamException();
        } catch (\Throwable $e) {
            $this->assertInstanceOf(InvalidParamException::class, $e);
            $this->assertSame('Invalid Parameter', $e->getMessage());
            $this->assertNull($e->getParam());
        }

        try {
            throw new InvalidParamException('Invalid Param', 'prop');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(InvalidParamException::class, $e);
            $this->assertSame('prop', $e->getParam());
            $this->assertSame('Invalid Param', $e->getMessage());
        }

        try {
            throw new InvalidParamException(null, 'prop');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(InvalidParamException::class, $e);
            $this->assertSame('prop', $e->getParam());
            $this->assertSame('Invalid Parameter: prop', $e->getMessage());
        }
    }
}
