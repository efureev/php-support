<?php

declare(strict_types=1);

namespace Php\Support\Tests\Testing;

use Php\Support\Testing\AdditionalAssertionsTrait;
use Php\Support\Traits\Maker;
use Php\Support\Traits\Thrower;
use Php\Support\Traits\Whener;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;

final class AdditionalAssertionsTraitTest extends TestCase
{
    use AdditionalAssertionsTrait;

    #[Test]
    public function acceptsASingleTraitOnAnInstance(): void
    {
        static::assertClassUsesTraits(new TraitsSubject(), Maker::class);
    }

    #[Test]
    public function acceptsSeveralTraitsOnAClassName(): void
    {
        static::assertClassUsesTraits(TraitsSubject::class, [Maker::class, Thrower::class]);
    }

    #[Test]
    public function findsTraitsInheritedFromAParent(): void
    {
        static::assertClassUsesTraits(TraitsChild::class, [Maker::class, Thrower::class]);
    }

    #[Test]
    public function failsWhenTheTraitIsMissing(): void
    {
        $this->expectException(AssertionFailedError::class);

        static::assertClassUsesTraits(TraitsSubject::class, Whener::class);
    }

    #[Test]
    public function usesTheGivenFailureMessage(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('custom failure');

        static::assertClassUsesTraits(TraitsSubject::class, Whener::class, 'custom failure');
    }
}
