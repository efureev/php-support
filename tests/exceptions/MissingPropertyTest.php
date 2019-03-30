<?php
declare(strict_types=1);

use Php\Support\Exceptions\MissingPropertyException;
use PHPUnit\Framework\TestCase;

/**
 * Class MissingPropertyTest
 */
final class MissingPropertyTest extends TestCase
{
    public function testThrow()
    {
        try {
            throw new MissingPropertyException('Invalid Arg');
        } catch (Throwable $e) {
            $this->assertInstanceOf(MissingPropertyException::class, $e);
            $this->assertSame('Invalid Arg', $e->getMessage());
        }

        try {
            throw new MissingPropertyException(null, 'test');
        } catch (Throwable $e) {
            $this->assertInstanceOf(MissingPropertyException::class, $e);
            $this->assertSame('Missing property', $e->getName());
            $this->assertSame('Missing property: "test"', $e->getMessage());
            $this->assertSame('test', $e->getProperty());
        }

        try {
            throw new MissingPropertyException();
        } catch (Throwable $e) {
            $this->assertInstanceOf(MissingPropertyException::class, $e);
            $this->assertSame('Missing property', $e->getName());
            $this->assertSame('Missing property', $e->getMessage());
        }


        try {
            throw new MissingPropertyException(null, 'test', ['key' => 'val']);
        } catch (Throwable $e) {
            $this->assertInstanceOf(MissingPropertyException::class, $e);
            $this->assertSame('Missing property', $e->getName());
            $this->assertSame('Missing property: "test"', $e->getMessage());
            $this->assertSame('test', $e->getProperty());
            $this->assertSame(['key' => 'val'], $e->getConfig());
        }
    }
}
