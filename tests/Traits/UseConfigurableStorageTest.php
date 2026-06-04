<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Exceptions\InvalidParamException;
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

    public function testConfigurableThrowsForUnknownKey(): void
    {
        $subject = $this->makeSubject();

        $this->expectException(InvalidParamException::class);

        $subject->configurable(['unknown' => 'value']);
    }

    public function testConfigurableSkipsUnknownKeyWhenNotThrowing(): void
    {
        $subject = $this->makeSubject();

        $result = $subject->configurable(['unknown' => 'value'], false);

        self::assertSame($subject, $result);
        self::assertNull($subject->get('unknown'));
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
