<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use ArrayAccess;
use Php\Support\Traits\UseStorage;
use PHPUnit\Framework\TestCase;

final class UseStorageTest extends TestCase
{
    private function makeSubject(): object
    {
        return new class implements ArrayAccess {
            use UseStorage;

            public ?string $title = null;
        };
    }

    public function testSetAndGetRealProperty(): void
    {
        $subject = $this->makeSubject();

        $subject->set('title', 'hello');

        self::assertSame('hello', $subject->get('title'));
        self::assertSame('hello', $subject->title);
    }

    public function testSetAndGetStorageValue(): void
    {
        $subject = $this->makeSubject();

        $subject->set('extra', 42);

        self::assertSame(42, $subject->get('extra'));
        self::assertSame(42, $subject->extra);
    }

    public function testGetReturnsDefaultForMissing(): void
    {
        $subject = $this->makeSubject();

        self::assertNull($subject->get('missing'));
        self::assertSame('def', $subject->get('missing', 'def'));
    }

    public function testMagicSetAndGet(): void
    {
        $subject = $this->makeSubject();

        $subject->extra = 'value';

        self::assertSame('value', $subject->extra);
    }

    public function testIssetForStorageAndProperty(): void
    {
        $subject = $this->makeSubject();

        self::assertFalse(isset($subject->extra));

        $subject->extra = 'value';
        $subject->title = 'real';

        self::assertTrue(isset($subject->extra));
        self::assertTrue(isset($subject->title));
    }

    public function testUnsetStorageValue(): void
    {
        $subject = $this->makeSubject();

        $subject->extra = 'value';
        unset($subject->extra);

        self::assertFalse(isset($subject->extra));
    }

    public function testArrayAccess(): void
    {
        $subject = $this->makeSubject();

        $subject['extra'] = 'value';

        self::assertTrue(isset($subject['extra']));
        self::assertSame('value', $subject['extra']);

        unset($subject['extra']);

        self::assertFalse(isset($subject['extra']));
    }

    public function testStorageDoesNotExposeStorageProperty(): void
    {
        $subject = $this->makeSubject();

        self::assertFalse($subject->propExists('storage'));
    }
}
