<?php

declare(strict_types=1);

namespace Php\Support\Tests\Exceptions;

use Php\Support\Exceptions\MissingPropertyException;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class MissingPropertyTest
 */
final class MissingPropertyTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new MissingPropertyException('test');
        } catch (Throwable $e) {
            $this->assertInstanceOf(MissingPropertyException::class, $e);
            $this->assertSame('Missing property: test', $e->getMessage());
            $this->assertSame('test', $e->property);
        }
    }
}
