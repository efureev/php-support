<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use Php\Support\ConditionalHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConditionalHandlerTest extends TestCase
{
    #[Test]
    public function makeReturnsInstance(): void
    {
        $handler = ConditionalHandler::make(static fn () => 'value');

        self::assertInstanceOf(ConditionalHandler::class, $handler);
    }

    #[Test]
    public function resolveWithDefaultTrueConditionCallsHandler(): void
    {
        $handler = ConditionalHandler::make(static fn () => 'result');

        self::assertSame('result', $handler->resolve());
    }

    #[Test]
    public function resolveWithFalseBoolConditionReturnsNull(): void
    {
        $called  = false;
        $handler = ConditionalHandler::make(
            static function () use (&$called) {
                $called = true;

                return 'result';
            },
            false
        );

        self::assertNull($handler->resolve());
        self::assertFalse($called);
    }

    #[Test]
    public function resolveWithTrueBoolConditionCallsHandler(): void
    {
        $handler = ConditionalHandler::make(static fn () => 'ok', true);

        self::assertSame('ok', $handler->resolve());
    }

    #[Test]
    public function handleIfReturnsNewInstance(): void
    {
        $handler = ConditionalHandler::make(static fn () => 'x');
        $new     = $handler->handleIf(true);

        self::assertInstanceOf(ConditionalHandler::class, $new);
        self::assertNotSame($handler, $new);
    }

    #[Test]
    public function handleIfWithFalseBoolPreventsHandler(): void
    {
        $handler = ConditionalHandler::make(static fn () => 'x')->handleIf(false);

        self::assertNull($handler->resolve());
    }

    #[Test]
    public function handleIfWithClosureConditionTrue(): void
    {
        $handler = ConditionalHandler::make(static fn () => 'allowed')
            ->handleIf(static fn () => true);

        self::assertSame('allowed', $handler->resolve());
    }

    #[Test]
    public function handleIfWithClosureConditionFalse(): void
    {
        $handler = ConditionalHandler::make(static fn () => 'allowed')
            ->handleIf(static fn () => false);

        self::assertNull($handler->resolve());
    }

    #[Test]
    public function conditionClosureReceivesParams(): void
    {
        $handler = ConditionalHandler::make(
            static fn (int $a, int $b) => $a + $b,
            static fn (int $a, int $b) => $a > $b,
        );

        self::assertSame(5, $handler->resolve(3, 2));
        self::assertNull($handler->resolve(1, 2));
    }

    #[Test]
    public function handlerReceivesParams(): void
    {
        $handler = ConditionalHandler::make(static fn (string $name) => "hello {$name}");

        self::assertSame('hello world', $handler->resolve('world'));
    }

    #[Test]
    public function invokeDelegatesToResolve(): void
    {
        $handler = ConditionalHandler::make(static fn (int $x) => $x * 2);

        self::assertSame(8, $handler(4));
    }

    #[Test]
    public function invokeRespectsCondition(): void
    {
        $handler = ConditionalHandler::make(static fn () => 'value', false);

        self::assertNull($handler());
    }

    #[Test]
    public function constructorWithClosureCondition(): void
    {
        $handler = new ConditionalHandler(
            static fn () => 'done',
            static fn (bool $flag) => $flag,
        );

        self::assertSame('done', $handler->resolve(true));
        self::assertNull($handler->resolve(false));
    }
}
