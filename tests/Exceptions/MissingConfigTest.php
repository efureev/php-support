<?php

declare(strict_types=1);

namespace Php\Support\Tests\Exceptions;

use Php\Support\Exceptions\MissingConfigException;
use PHPUnit\Framework\TestCase;

/**
 * Class MissingConfigTest
 */
final class MissingConfigTest extends TestCase
{
    public function testDefaultMessage(): void
    {
        $exception = new MissingConfigException();

        self::assertSame('Missing Config', $exception->getMessage());
        self::assertNull($exception->needKey);
        self::assertSame([], $exception->config);
    }

    public function testNeedKeyIsReportedInTheMessage(): void
    {
        $exception = new MissingConfigException(['driver' => 'pgsql'], 'host');

        self::assertSame('Missing Config: host', $exception->getMessage());
        self::assertSame('host', $exception->needKey);
        self::assertSame(['driver' => 'pgsql'], $exception->config);
    }

    public function testCustomMessage(): void
    {
        $exception = new MissingConfigException([], 'host', 'Database config');

        self::assertSame('Database config: host', $exception->getMessage());
    }
}
