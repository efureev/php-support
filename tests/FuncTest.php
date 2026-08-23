<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use Php\Support\Func;
use Php\Support\Tests\Global\RemoteCallSubject;
use Php\Support\Tests\Global\RemoteCallTraitHolder;
use Php\Support\Structures\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * The facade holds the logic; the global functions in src/Global/base.php only forward to it.
 */
final class FuncTest extends TestCase
{
    /**
     * Global function => facade method it must forward to.
     *
     * @return array<string, array{string, string}>
     */
    public static function globalToFacade(): array
    {
        $pairs = [
            'value'                   => 'value',
            'dataGet'                 => 'dataGet',
            'mapValue'                => 'mapValue',
            'eachValue'               => 'eachValue',
            'when'                    => 'when',
            'classNamespace'          => 'classNamespace',
            'isTrue'                  => 'isTrue',
            'instance'                => 'instance',
            'class_basename'          => 'classBasename',
            'trait_uses_recursive'    => 'traitUsesRecursive',
            'does_trait_use'          => 'doesTraitUse',
            'class_uses_recursive'    => 'classUsesRecursive',
            'remoteStaticCall'        => 'remoteStaticCall',
            'remoteStaticCallOrThrow' => 'remoteStaticCallOrThrow',
            'remoteCall'              => 'remoteCall',
            'attributeToGetterMethod' => 'attributeToGetterMethod',
            'attributeToSetterMethod' => 'attributeToSetterMethod',
            'findGetterMethod'        => 'findGetterMethod',
            'findSetterMethodByProp'  => 'findSetterMethod',
            'public_property_exists'  => 'publicPropertyExists',
            'getPropertyValue'        => 'getPropertyValue',
        ];

        $result = [];
        foreach ($pairs as $global => $method) {
            $result[$global] = [
                $global,
                $method,
            ];
        }

        return $result;
    }

    #[DataProvider('globalToFacade')]
    public function testEveryGlobalHasAFacadeCounterpart(string $global, string $method): void
    {
        self::assertTrue(function_exists($global), "global $global is missing");
        self::assertTrue(method_exists(Func::class, $method), "Func::$method is missing");
    }

    #[Test]
    public function theFacadeExposesOnlyStaticMethods(): void
    {
        $class = new \ReflectionClass(Func::class);

        self::assertTrue($class->isFinal(), 'the facade is not meant to be extended');

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            self::assertTrue($method->isStatic(), "Func::{$method->name} must be static");
        }
    }

    #[Test]
    public function globalsForwardTheirResult(): void
    {
        $object       = new \stdClass();
        $object->name = 'obj';

        self::assertSame(Func::value('x'), value('x'));
        self::assertSame(Func::dataGet(['a' => ['b' => 1]], 'a.b'), dataGet(['a' => ['b' => 1]], 'a.b'));
        self::assertSame(Func::isTrue('yes'), isTrue('yes'));
        self::assertSame(Func::classBasename(Func::class), class_basename(Func::class));
        self::assertSame(Func::classNamespace(Func::class), classNamespace(Func::class));
        self::assertSame(Func::attributeToGetterMethod('name'), attributeToGetterMethod('name'));
        self::assertSame(Func::getPropertyValue($object, 'name'), getPropertyValue($object, 'name'));
        self::assertSame(Func::when(true, 'yes'), when(true, 'yes'));
    }

    #[Test]
    public function facadeIsUsableWithoutTheGlobals(): void
    {
        self::assertSame('Privet', Func::value(static fn() => 'Privet'));
        self::assertSame([2, 4], array_values(Func::mapValue(static fn($v) => $v * 2, [1, 2])));
        self::assertSame(1, Func::dataGet(new ArrayCollection([['a' => 1]]), '0.a'));
        self::assertNull(Func::instance(null));
        self::assertInstanceOf(\stdClass::class, Func::instance(\stdClass::class));
    }

    #[Test]
    public function findSetterMethodDropsTheByPropSuffix(): void
    {
        // the global keeps its old name; the facade uses the one symmetric with findGetterMethod
        $subject = new class {
            public function setName(string $value): void
            {
            }
        };

        self::assertSame('setName', Func::findSetterMethod($subject, 'name'));
        self::assertNull(Func::findSetterMethod($subject, 'missing'));
    }

    #[Test]
    public function globalWrappersHoldNoLogic(): void
    {
        // a wrapper that grew a body would drift from the facade; keep them one-liners
        $file = file(__DIR__ . '/../src/Global/base.php') ?: [];

        foreach ($file as $number => $line) {
            $trimmed = ltrim($line);

            if (!str_contains($line, 'Func::') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/')) {
                continue;
            }

            self::assertMatchesRegularExpression(
                '/^\s{8}(return )?Func::\w+\(/',
                $line,
                'line ' . ($number + 1) . ' of base.php should be a plain forward'
            );
        }
    }

    #[Test]
    public function everyGlobalWrapperForwards(): void
    {
        $object       = new \stdClass();
        $object->name = 'obj';
        $subject      = new class {
            public string $title = 'a title';

            public function getName(): string
            {
                return 'name';
            }

            public function setName(string $value): void
            {
            }
        };

        self::assertSame('x', value('x'));
        self::assertSame(1, dataGet(['a' => ['b' => 1]], 'a.b'));
        self::assertSame([2, 4], array_values(mapValue(static fn($v) => $v * 2, [1, 2])));

        $seen = [];
        eachValue(
            static function ($v) use (&$seen): void {
                $seen[] = $v;
            },
            [
                1,
                2,
            ]
        );
        self::assertSame([1, 2], $seen);

        self::assertSame('yes', when(true, 'yes'));
        self::assertSame('Php\Support', classNamespace(Func::class));
        self::assertTrue(isTrue('yes'));
        self::assertInstanceOf(\stdClass::class, instance(\stdClass::class));
        self::assertSame('Func', class_basename(Func::class));
        self::assertIsArray(trait_uses_recursive(RemoteCallTraitHolder::class));
        self::assertTrue(does_trait_use(RemoteCallTraitHolder::class, \Php\Support\Traits\Maker::class));
        self::assertIsArray(class_uses_recursive(RemoteCallTraitHolder::class));
        self::assertSame('static:x', remoteStaticCall(RemoteCallSubject::class, 'staticGreet', 'x'));
        self::assertSame('static:y', remoteStaticCallOrThrow(RemoteCallSubject::class, 'staticGreet', 'y'));
        self::assertSame('instance:z', remoteCall(new RemoteCallSubject(), 'greet', 'z'));
        self::assertSame('getName', attributeToGetterMethod('name'));
        self::assertSame('setName', attributeToSetterMethod('name'));
        self::assertSame('getName', findGetterMethod($subject, 'name'));
        self::assertSame('setName', findSetterMethodByProp($subject, 'name'));
        self::assertSame('title', public_property_exists($subject, 'title'));
        self::assertSame('obj', getPropertyValue($object, 'name'));
    }

    #[Test]
    public function dataGetStopsAtANullSegment(): void
    {
        $target = ['a' => 1];

        self::assertSame($target, Func::dataGet($target, [null]));
    }
}
