<?php

declare(strict_types=1);

namespace Php\Support\Tests\Testing;

use Php\Support\Testing\TestingHelper;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TestingHelperTest extends TestCase
{
    use TestingHelper;

    #[Test]
    public function runsAProtectedMethodOnAnInstance(): void
    {
        $subject = new TestingHelperSubject();

        self::assertSame('protected:x', static::runProtectedMethod($subject, 'hidden', 'x'));
    }

    #[Test]
    public function runsAPrivateMethodOnAnInstance(): void
    {
        $subject = new TestingHelperSubject();

        self::assertSame(42, static::runProtectedMethod($subject, 'secretNumber'));
    }

    #[Test]
    public function runsAStaticProtectedMethodByClassName(): void
    {
        self::assertSame('static-hidden', static::runProtectedMethod(TestingHelperSubject::class, 'hiddenStatic'));
    }

    #[Test]
    public function readsPropertiesOfEveryVisibility(): void
    {
        $subject = new TestingHelperSubject();

        self::assertSame('public', static::getProperty($subject, 'openValue'));
        self::assertSame('protected', static::getProperty($subject, 'guardedValue'));
        self::assertSame('private', static::getProperty($subject, 'secretValue'));
    }

    #[Test]
    public function failsForAnUnknownMethod(): void
    {
        $this->expectException(\ReflectionException::class);

        static::runProtectedMethod(new TestingHelperSubject(), 'nope');
    }

    #[Test]
    public function failsForAnUnknownProperty(): void
    {
        $this->expectException(\ReflectionException::class);

        static::getProperty(new TestingHelperSubject(), 'nope');
    }
}
