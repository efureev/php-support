<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use Php\Support\Exceptions\UnknownMethodException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class UnknownMethodTest
 */
final class UnknownMethodTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new UnknownMethodException('Invalid Arg');
        } catch (Throwable $e) {
            $this->assertInstanceOf(UnknownMethodException::class, $e);
            $this->assertSame('Invalid Arg', $e->getMessage());
        }

        try {
            throw new UnknownMethodException(null, 'test');
        } catch (Throwable $e) {
            $this->assertInstanceOf(UnknownMethodException::class, $e);
            $this->assertSame('Unknown method', $e->getName());
            $this->assertSame('Unknown method: "test"', $e->getMessage());
            $this->assertSame('test', $e->getMethod());
        }

        try {
            throw new UnknownMethodException();
        } catch (Throwable $e) {
            $this->assertInstanceOf(UnknownMethodException::class, $e);
            $this->assertSame('Unknown method', $e->getName());
            $this->assertSame('Unknown method', $e->getMessage());
        }
    }
}
