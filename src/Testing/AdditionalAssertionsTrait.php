<?php

declare(strict_types=1);

namespace Php\Support\Testing;

use Php\Support\Func;

/**
 * Extra assertions for your own test suite.
 *
 * Requires phpunit/phpunit, which this package only suggests - see composer.json. The trait is
 * shipped in src/ rather than autoload-dev so that consumers can use it in their tests.
 *
 * @mixin \PHPUnit\Framework\TestCase
 */
trait AdditionalAssertionsTrait
{
    /**
     * Asserts that the class uses the expected traits, including ones inherited from a parent
     * or pulled in by another trait.
     *
     * @param object|class-string $class
     * @param string|string[] $expectedTraits
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     *
     * @example
     *  static::assertClassUsesTraits(new Model(), [HasCasts::class, NestedSetTrait::class]);
     */
    public static function assertClassUsesTraits(
        object|string $class,
        string|array $expectedTraits,
        string $message = ''
    ): void {
        // this used to reimplement the recursion inline, duplicating helpers the package ships
        $uses = Func::classUsesRecursive($class);

        foreach ((array)$expectedTraits as $trait) {
            static::assertContains(
                $trait,
                $uses,
                $message === '' ? "Class does not use trait $trait" : $message
            );
        }
    }
}
