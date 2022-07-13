<?php

declare(strict_types=1);

namespace Php\Support\Testing;

use function array_merge;
use function array_reverse;
use function array_values;
use function class_parents;
use function class_uses;
use function get_class;
use function is_object;

/**
 * @mixin \PHPUnit\Framework\TestCase
 */
trait AdditionalAssertionsTrait
{
    /**
     * Asserts that passed class uses expected traits.
     *
     * @param string $class
     * @param string|string[] $expected_traits
     * @param string $message
     *
     * @return void
     * @throws \InvalidArgumentException
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     *
     * @example
     *  static::assertClassUsesTraits(new Class(), [HasCasts::class, NestedSetTrait::class,]);
     */
    public static function assertClassUsesTraits($class, $expected_traits, string $message = ''): void
    {
        /**
         * Returns all traits used by a trait and its traits.
         *
         * @param string $trait
         *
         * @return string[]
         */
        $trait_uses_recursive = static function ($trait) use (&$trait_uses_recursive) {
            $traits = class_uses($trait);
            $tt     = [[]];
            foreach ($traits as $trait_iterate) {
                $tt[] = $trait_uses_recursive($trait_iterate);
            }
            $traits = array_merge($traits, ...$tt);
            return $traits;
        };

        /**
         * Returns all traits used by a class, its subclasses and trait of their traits.
         *
         * @param object|string $class
         *
         * @return array
         */
        $class_uses_recursive = static function ($class) use ($trait_uses_recursive) {
            if (is_object($class)) {
                $class = get_class($class);
            }
            $results = [[]];
            foreach (array_reverse(class_parents($class)) + [$class => $class] as $class_iterate) {
                $results[] = $trait_uses_recursive($class_iterate);
            }
            return array_values(array_merge(...$results));
        };

        $uses = $class_uses_recursive($class);
        $uses = array_flip($uses);

        foreach ((array)$expected_traits as $k => $trait_class) {
            static::assertArrayHasKey(
                $trait_class,
                $uses,
                $message === ''
                    ? 'Class does not uses passed traits'
                    : $message
            );
        }
    }
}
