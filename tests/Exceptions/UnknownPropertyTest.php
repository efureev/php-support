<?php

declare(strict_types=1);

namespace Php\Support\Tests\Exceptions;

use Php\Support\Exceptions\UnknownPropertyException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class UnknownPropertyTest
 */
final class UnknownPropertyTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new UnknownPropertyException('test');
        } catch (Throwable $e) {
            $this->assertInstanceOf(UnknownPropertyException::class, $e);
            $this->assertSame('Unknown property: test', $e->getMessage());
            $this->assertSame('test', $e->property);
        }
    }
}
