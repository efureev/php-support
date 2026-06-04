<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Exceptions\MissingPropertyException;
use Php\Support\Traits\ReadOnlyProperties;
use PHPUnit\Framework\TestCase;

final class ReadOnlyPropertiesTest extends TestCase
{
    private function makeSubject(): object
    {
        return new class {
            use ReadOnlyProperties;

            protected string $name = 'John';
            protected ?int $age    = null;
        };
    }

    public function testReadsExistingProperty(): void
    {
        $subject = $this->makeSubject();

        self::assertSame('John', $subject->name);
        self::assertNull($subject->age);
    }

    public function testThrowsOnMissingProperty(): void
    {
        $subject = $this->makeSubject();

        $this->expectException(MissingPropertyException::class);

        /** @phpstan-ignore-next-line */
        $subject->unknown;
    }

    public function testMissingPropertyExceptionMessageContainsKey(): void
    {
        $subject = $this->makeSubject();

        try {
            /** @phpstan-ignore-next-line */
            $subject->unknown;
            self::fail('Exception was not thrown');
        } catch (MissingPropertyException $exception) {
            self::assertStringContainsString('unknown', $exception->getMessage());
        }
    }
}
