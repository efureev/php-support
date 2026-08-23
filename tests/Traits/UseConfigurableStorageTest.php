<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Traits\UseConfigurableStorage;
use PHPUnit\Framework\TestCase;

final class UseConfigurableStorageTest extends TestCase
{
    private function makeSubject(): object
    {
        return new class {
            use UseConfigurableStorage;

            public ?string $title = null;
        };
    }

    public function testConfigurableWritesRealProperty(): void
    {
        $subject = $this->makeSubject();

        $result = $subject->configurable(['title' => 'real']);

        self::assertSame($subject, $result);
        self::assertSame('real', $subject->title);
        self::assertSame('real', $subject->get('title'));
    }

    public function testConfigurableStoresUnknownKeyInStorage(): void
    {
        $subject = $this->makeSubject();

        $result = $subject->configurable(['unknown' => 'value']);

        self::assertSame($subject, $result);
        self::assertSame('value', $subject->get('unknown'));
        self::assertSame('value', $subject->unknown);
        self::assertTrue($subject->propExists('unknown'));
    }

    public function testConfigurableStoresUnknownKeyWhenNotThrowing(): void
    {
        $subject = $this->makeSubject();

        $result = $subject->configurable(['unknown' => 'value'], false);

        self::assertSame($subject, $result);
        self::assertSame('value', $subject->get('unknown'));
    }

    public function testConfigurableMixesPropertiesAndStorage(): void
    {
        $subject = $this->makeSubject();

        $subject->configurable(['title' => 'real', 'extra' => 42]);

        self::assertSame('real', $subject->title);
        self::assertSame(42, $subject->get('extra'));
    }

    public function testConfigurablePrefersSetterOverStorage(): void
    {
        $subject = new class {
            use UseConfigurableStorage;

            public ?string $title = null;

            public function setTitle(string $value): void
            {
                $this->title = 'via-setter:' . $value;
            }
        };

        $subject->configurable(['title' => 'x']);

        self::assertSame('via-setter:x', $subject->title);
    }

    public function testStorageMethodsStillWork(): void
    {
        $subject = $this->makeSubject();

        $subject->set('extra', 'value');

        self::assertTrue($subject->propExists('extra'));
        self::assertSame('value', $subject->get('extra'));
        self::assertSame('value', $subject->extra);
    }

    public function testStorageIgnoresProtectedStorageProperty(): void
    {
        $subject = $this->makeSubject();

        self::assertFalse($subject->propExists('storage'));
    }
}
