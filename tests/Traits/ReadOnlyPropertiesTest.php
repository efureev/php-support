<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Exceptions\MissingPropertyException;
use Php\Support\Traits\ReadOnlyProperties;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReadOnlyPropertiesTest extends TestCase
{
    private function makeSubject(): object
    {
        return new class {
            use ReadOnlyProperties;

            protected string $name = 'John';
            protected ?int $age    = null;
            private string $secret = 's3cr3t';

            protected function readOnlyProperties(): array
            {
                return [
                    'name',
                    'age',
                ];
            }
        };
    }

    #[Test]
    public function readsAnAllowedProperty(): void
    {
        $subject = $this->makeSubject();

        self::assertSame('John', $subject->name);
        self::assertNull($subject->age);
    }

    #[Test]
    public function hidesPropertiesThatAreNotListed(): void
    {
        // before 6.0 the trait returned every declared property, so this leaked
        $subject = $this->makeSubject();

        $this->expectException(MissingPropertyException::class);

        /** @phpstan-ignore-next-line */
        $subject->secret;
    }

    #[Test]
    public function throwsOnMissingProperty(): void
    {
        $subject = $this->makeSubject();

        $this->expectException(MissingPropertyException::class);

        /** @phpstan-ignore-next-line */
        $subject->unknown;
    }

    #[Test]
    public function missingPropertyExceptionMessageContainsKey(): void
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

    #[Test]
    public function issetFollowsTheSameRules(): void
    {
        $subject = $this->makeSubject();

        self::assertTrue(isset($subject->name));
        self::assertFalse(isset($subject->age), 'a null value is not set');
        self::assertFalse(isset($subject->secret), 'not listed, so not visible');
        self::assertFalse(isset($subject->unknown));
    }

    #[Test]
    public function anEmptyAllowListExposesNothing(): void
    {
        $subject = new class {
            use ReadOnlyProperties;

            protected string $name = 'John';
        };

        $this->expectException(MissingPropertyException::class);

        /** @phpstan-ignore-next-line */
        $subject->name;
    }
}
