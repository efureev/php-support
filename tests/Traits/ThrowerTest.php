<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Traits\Thrower;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ThrowerTest extends TestCase
{
    public function testThrow(): void
    {
        $this->expectException(ThrowerTestException::class);
        $this->expectExceptionMessage('boom');

        ThrowerTestException::throw('boom');
    }

    public function testThrowIfTrue(): void
    {
        $this->expectException(ThrowerTestException::class);
        $this->expectExceptionMessage('failed');

        ThrowerTestException::throwIf(true, 'failed');
    }

    public function testThrowIfFalseDoesNothing(): void
    {
        ThrowerTestException::throwIf(false, 'failed');

        $this->expectNotToPerformAssertions();
    }

    public function testThrowIfReturnReturnsTrueWhenNotThrown(): void
    {
        self::assertTrue(ThrowerTestException::throwIfReturn(false, 'failed'));
    }

    public function testThrowIfReturnThrowsWhenTrue(): void
    {
        $this->expectException(ThrowerTestException::class);

        ThrowerTestException::throwIfReturn(true, 'failed');
    }
}

class ThrowerTestException extends RuntimeException
{
    use Thrower;
}
