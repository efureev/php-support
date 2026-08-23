<?php

declare(strict_types=1);

namespace Php\Support\Tests\Exceptions;

use Php\Support\Exceptions\InvalidCallException;
use Php\Support\Exceptions\InvalidConfigException;
use Php\Support\Exceptions\InvalidValueException;
use Php\Support\Exceptions\MethodNotAllowedException;
use Php\Support\Exceptions\MissingMethodException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Covers the exception classes that carried no tests at all.
 */
final class RemainingExceptionsTest extends TestCase
{
    #[Test]
    public function invalidCallException(): void
    {
        $exception = new InvalidCallException('bad call');

        self::assertSame('bad call', $exception->getMessage());
        self::assertSame('Invalid Call', $exception->getName());
        self::assertInstanceOf(\BadMethodCallException::class, $exception);
    }

    #[Test]
    public function invalidValueException(): void
    {
        $exception = new InvalidValueException('bad value');

        self::assertSame('bad value', $exception->getMessage());
        self::assertSame('Invalid Return Value', $exception->getName());
        self::assertInstanceOf(\UnexpectedValueException::class, $exception);
    }

    #[Test]
    public function invalidConfigException(): void
    {
        $exception = new InvalidConfigException(['driver' => 'pgsql']);

        self::assertSame('Invalid Configuration', $exception->getMessage());
        self::assertSame(['driver' => 'pgsql'], $exception->config);

        $custom = new InvalidConfigException([], 'Broken config');
        self::assertSame('Broken config', $custom->getMessage());
    }

    #[Test]
    public function methodNotAllowedException(): void
    {
        $exception = new MethodNotAllowedException('PATCH');

        self::assertSame('Method Not Allowed: PATCH', $exception->getMessage());

        $bare = new MethodNotAllowedException('PATCH', '');
        self::assertSame('PATCH', $bare->getMessage());
    }

    #[Test]
    public function missingMethodException(): void
    {
        $exception = new MissingMethodException('Acme::run');

        self::assertSame('Missing method: Acme::run', $exception->getMessage());
        self::assertSame('Missing method', $exception->getName());

        $custom = new MissingMethodException('Acme::run', 'Nope');
        self::assertSame('Nope', $custom->getMessage());
    }

    #[Test]
    public function missingMethodExceptionCanThrowItself(): void
    {
        $this->expectException(MissingMethodException::class);
        $this->expectExceptionMessage('Missing method: Acme::run');

        MissingMethodException::throw('Acme::run');
    }
}
