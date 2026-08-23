<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Traits\TraitBooter;
use Php\Support\Traits\TraitInitializer;
use Php\Support\Traits\Whener;
use PHPUnit\Framework\TestCase;

final class TraitWhenerTest extends TestCase
{
    public function testBootTrait(): void
    {
        $class = new class {
            use Whener;
        };

        self::assertEquals(1, $class->when(true, fn()=>1));
    }

    public function testReturnsCallbackResultWhenValueIsTruthy(): void
    {
        $class = $this->subject();

        self::assertSame('called:5', $class->when(5, static fn($self, $value) => 'called:' . $value));
    }

    public function testReturnsSelfWhenTheCallbackReturnsNull(): void
    {
        $class = $this->subject();

        self::assertSame($class, $class->when(true, static fn() => null));
    }

    public function testFallsBackToTheDefaultWhenValueIsFalsy(): void
    {
        $class = $this->subject();

        $default = static fn($self, $value) => 'default:' . $value;

        self::assertSame('default:0', $class->when(0, static fn() => 'never', $default));
    }

    public function testReturnsSelfWhenTheDefaultReturnsNull(): void
    {
        $class = $this->subject();

        self::assertSame($class, $class->when(false, static fn() => 'never', static fn() => null));
    }

    public function testReturnsSelfWhenFalsyAndNoDefaultGiven(): void
    {
        $class = $this->subject();

        self::assertSame($class, $class->when(false, static fn() => 'never'));
    }

    public function testPassesItselfToTheCallback(): void
    {
        $class = $this->subject();

        self::assertSame($class, $class->when(true, static fn($self) => $self));
    }

    private function subject(): object
    {
        return new class {
            use Whener;
        };
    }
}
