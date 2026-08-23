<?php

declare(strict_types=1);

namespace Php\Support\Tests\Global;

use Php\Support\Exceptions\MissingMethodException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Covers the reflection-flavoured global helpers, which had no tests.
 */
final class RemoteCallTest extends TestCase
{
    #[Test]
    public function remoteStaticCall(): void
    {
        self::assertSame('static:x', remoteStaticCall(RemoteCallSubject::class, 'staticGreet', 'x'));
        self::assertSame('static:x', remoteStaticCall(new RemoteCallSubject(), 'staticGreet', 'x'));

        self::assertNull(remoteStaticCall(null, 'staticGreet'));
        self::assertNull(remoteStaticCall(RemoteCallSubject::class, 'missing'));
        self::assertNull(remoteStaticCall('Acme\\NoSuchClass', 'staticGreet'));
    }

    #[Test]
    public function remoteStaticCallOrThrowReturnsTheResult(): void
    {
        self::assertSame('static:y', remoteStaticCallOrThrow(RemoteCallSubject::class, 'staticGreet', 'y'));
    }

    #[Test]
    public function remoteStaticCallOrThrowRejectsAnEmptyTarget(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Target Class is absent');

        remoteStaticCallOrThrow(null, 'staticGreet');
    }

    #[Test]
    public function remoteStaticCallOrThrowRejectsAMissingMethod(): void
    {
        $this->expectException(MissingMethodException::class);

        remoteStaticCallOrThrow(RemoteCallSubject::class, 'missing');
    }

    #[Test]
    public function misspelledAliasStillWorks(): void
    {
        self::assertSame('static:z', remoteStaticCallOrTrow(RemoteCallSubject::class, 'staticGreet', 'z'));
    }

    #[Test]
    public function remoteCall(): void
    {
        self::assertSame('instance:x', remoteCall(new RemoteCallSubject(), 'greet', 'x'));
        self::assertNull(remoteCall(null, 'greet'));
        self::assertNull(remoteCall(new RemoteCallSubject(), 'missing'));
    }

    #[Test]
    public function accessorNameHelpers(): void
    {
        self::assertSame('getName', attributeToGetterMethod('name'));
        self::assertSame('setName', attributeToSetterMethod('name'));

        $subject = new RemoteCallSubject();

        self::assertSame('getName', findGetterMethod($subject, 'name'));
        self::assertNull(findGetterMethod($subject, 'missing'));
        self::assertSame('setName', findSetterMethodByProp($subject, 'name'));
        self::assertNull(findSetterMethodByProp($subject, 'missing'));
    }

    #[Test]
    public function publicPropertyHelpers(): void
    {
        $subject = new RemoteCallSubject();

        self::assertSame('title', public_property_exists($subject, 'title'));
        self::assertSame('title', public_property_exists($subject, 'Title'));
        self::assertNull(public_property_exists($subject, 'hidden'));
        self::assertNull(public_property_exists($subject, 'missing'));

        self::assertSame('a title', getPropertyValue($subject, 'title'));
        self::assertNull(getPropertyValue($subject, 'hidden'));
        self::assertNull(getPropertyValue($subject, 'missing'));
    }

    #[Test]
    public function classNamespaceHelper(): void
    {
        self::assertSame(__NAMESPACE__, classNamespace(RemoteCallSubject::class));
        self::assertSame(__NAMESPACE__, classNamespace(new RemoteCallSubject()));
        self::assertSame('', classNamespace('NoNamespaceClass'));
    }
}
