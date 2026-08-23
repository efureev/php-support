<?php

declare(strict_types=1);

namespace Php\Support\Tests\Exceptions;

use Php\Support\Exceptions\ExceptionInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class ExceptionInterfaceTest
 */
final class ExceptionInterfaceTest extends TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public static function packageExceptions(): array
    {
        $result = [];

        foreach (glob(__DIR__ . '/../../src/Exceptions/*.php') ?: [] as $file) {
            $class = 'Php\\Support\\Exceptions\\' . basename($file, '.php');

            if ((new ReflectionClass($class))->isInterface()) {
                continue;
            }

            $result[$class] = [$class];
        }

        return $result;
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('packageExceptions')]
    public function testEveryExceptionImplementsTheMarker(string $class): void
    {
        self::assertTrue(
            is_subclass_of($class, ExceptionInterface::class),
            "$class must implement " . ExceptionInterface::class
        );
    }

    #[Test]
    public function everyExceptionIsCatchableThroughTheMarker(): void
    {
        // the concrete classes extend different SPL exceptions; the marker is the only
        // thing that lets a consumer catch all of them at once
        $thrown = [
            new \Php\Support\Exceptions\InvalidParamException('logic'),
            new \Php\Support\Exceptions\UnknownMethodException('method'),
            new \Php\Support\Exceptions\NotSupportedException('cls'),
            new \Php\Support\Exceptions\InvalidValueException('value'),
        ];

        foreach ($thrown as $exception) {
            $caught = null;

            try {
                throw $exception;
            } catch (ExceptionInterface $e) {
                $caught = $e;
            }

            self::assertSame($exception, $caught);
        }
    }

    #[Test]
    public function markerDoesNotCatchForeignExceptions(): void
    {
        $this->expectException(\RuntimeException::class);

        try {
            throw new \RuntimeException('foreign');
        } catch (ExceptionInterface) {
            self::fail('a foreign exception must not match the package marker');
        }
    }
}
