<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait TraitBooter
 */
trait TraitBooter
{
    /**
     * The array of booted classes.
     *
     * @var class-string[]
     */
    protected static array $booted = [];

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @return class-string[]
     */
    protected static function bootTraits(): array
    {
        $class = static::class;

        $booted = [];

        foreach ($traits = class_uses_recursive($class) as $trait) {
            $method = 'boot' . class_basename($trait);

            if (!isset($booted[$method]) && method_exists($class, $method)) {
                forward_static_call([$class, $method]);

                $booted[$method] = true;
            }
        }

        return $traits;
    }

    protected function bootIfNotBooted(): void
    {
        if (!isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            static::booting();
            static::boot();
            static::booted();
        }
    }

    /**
     * Perform any actions required before the instance boots.
     */
    protected static function booting(): void
    {
        //
    }

    /**
     * Bootstrap the instance and its traits.
     */
    protected static function boot(): void
    {
        static::bootTraits();
    }

    /**
     * Perform any actions required after the instance boots.
     */
    protected static function booted(): void
    {
        //
    }

    /**
     * Clear the list of booted models so they will be re-booted.
     */
    public static function clearBooted(): void
    {
        static::$booted = [];
    }
}
