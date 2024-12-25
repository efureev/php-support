<?php

declare(strict_types=1);

namespace Php\Support\Tests\Exceptions;

use Php\Support\Exceptions\InvalidParamException;
use PHPUnit\Framework\TestCase;

/**
 * Class InvalidParamTest
 */
final class InvalidParamTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new InvalidParamException('Invalid Param');
        } catch (InvalidParamException $e) {
            $this->assertInstanceOf(InvalidParamException::class, $e);
            $this->assertSame('Invalid Param', $e->getMessage());
            $this->assertSame('Invalid Parameter', $e->getName());
        }

        try {
            throw new InvalidParamException();
        } catch (InvalidParamException $e) {
            $this->assertInstanceOf(InvalidParamException::class, $e);
            $this->assertSame('Invalid Parameter', $e->getMessage());
            $this->assertSame('Invalid Parameter', $e->getName());
            $this->assertNull($e->name);
        }

        try {
            throw new InvalidParamException('Invalid Param', 'prop');
        } catch (InvalidParamException $e) {
            $this->assertInstanceOf(InvalidParamException::class, $e);
            $this->assertSame('prop', $e->name);
            $this->assertSame('Invalid Param', $e->getMessage());
            $this->assertSame('Invalid Parameter', $e->getName());
        }

        try {
            throw new InvalidParamException(null, 'prop');
        } catch (InvalidParamException $e) {
            $this->assertInstanceOf(InvalidParamException::class, $e);
            $this->assertSame('prop', $e->name);
            $this->assertSame('Invalid Parameter', $e->getName());
            $this->assertSame('Invalid Parameter: prop', $e->getMessage());
        }
    }
}
