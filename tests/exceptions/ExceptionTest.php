<?php

declare(strict_types=1);

use Php\Support\Exceptions\Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class ExceptionTest
 */
final class ExceptionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testThrow()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid Arg');

        throw new Exception('Invalid Arg');
    }

    public function testThrow2()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Exception');

        throw new Exception();
    }
}
