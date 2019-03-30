<?php
declare(strict_types=1);

use Php\Support\Exceptions\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class InvalidArgumentTest
 */
final class InvalidArgumentTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new InvalidArgumentException('Invalid Arg');
        } catch (Throwable $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
            $this->assertSame('Invalid Arg', $e->getMessage());
        }

        try {
            throw new InvalidArgumentException();
        } catch (Throwable $e) {
            $this->assertInstanceOf(InvalidArgumentException::class, $e);
            $this->assertSame('Invalid Argument', $e->getName());
            $this->assertSame('Invalid Argument', $e->getMessage());
        }
    }
}
