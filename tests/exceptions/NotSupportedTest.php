<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use Php\Support\Exceptions\NotSupportedException;
use PHPUnit\Framework\TestCase;

/**
 * Class MissingClassTest
 */
final class NotSupportedTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new NotSupportedException();
        } catch (Throwable $e) {
            $this->assertInstanceOf(NotSupportedException::class, $e);
            $this->assertSame('Not Supported', $e->getMessage());
        }

        try {
            throw new NotSupportedException(NotSupportedException::class);
        } catch (Throwable $e) {
            $this->assertInstanceOf(NotSupportedException::class, $e);
            $this->assertSame('Not Supported: ' . NotSupportedException::class, $e->getMessage());
        }

        try {
            throw new NotSupportedException(null, 'Test Message');
        } catch (Throwable $e) {
            $this->assertInstanceOf(NotSupportedException::class, $e);
            $this->assertSame('Test Message', $e->getMessage());
        }
        try {
            throw new NotSupportedException(NotSupportedException::class, 'Test Message');
        } catch (Throwable $e) {
            $this->assertInstanceOf(NotSupportedException::class, $e);
            $this->assertSame('Test Message: ' . NotSupportedException::class, $e->getMessage());
        }
    }
}
