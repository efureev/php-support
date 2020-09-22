<?php

declare(strict_types=1);

namespace Php\Support\Traits;

trait TraitBooter
{
    /**
     * The array of booted classes.
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @var array
     */
    //    protected static $traitInitializers = [];

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @return array
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

    protected function bootIfNotBooted()
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
     *
     * @return void
     */
    protected static function booting(): void
    {
        //
    }

    /**
     * Bootstrap the instance and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        static::bootTraits();
    }

    /**
     * Perform any actions required after the instance boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        //
    }

    /**
     * Clear the list of booted models so they will be re-booted.
     *
     * @return void
     */
    public static function clearBooted()
    {
        static::$booted = [];
    }
}
