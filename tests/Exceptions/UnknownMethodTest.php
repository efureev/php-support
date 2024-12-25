<?php

declare(strict_types=1);

namespace Php\Support\Tests\Exceptions;

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
            throw new UnknownMethodException('method');
        } catch (Throwable $e) {
            $this->assertInstanceOf(UnknownMethodException::class, $e);
            $this->assertSame('Unknown method: method', $e->getMessage());
        }
    }
}
