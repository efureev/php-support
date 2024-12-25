<?php

declare(strict_types=1);

namespace Php\Support\Tests\Exceptions;

use Php\Support\Exceptions\MissingClassException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class MissingClassTest
 */
final class MissingClassTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new MissingClassException(MissingClassException::class);
        } catch (Throwable $e) {
            $this->assertInstanceOf(MissingClassException::class, $e);
            $this->assertSame('Missing Class: ' . MissingClassException::class, $e->getMessage());
        }

        try {
            throw new MissingClassException(MissingClassException::class, 'Test Message');
        } catch (Throwable $e) {
            $this->assertInstanceOf(MissingClassException::class, $e);
            $this->assertSame('Test Message: ' . MissingClassException::class, $e->getMessage());
        }
    }
}
