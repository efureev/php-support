<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait TraitInitializer
 * @package Php\Support\Traits
 */
trait TraitInitializer
{
    use TraitBooter {
        bootTraits as parentBootTraits;
        bootIfNotBooted as parentBootIfNotBooted;
    }

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @var array
     */
    protected static array $traitInitializers = [];

    protected static function bootTraits(): array
    {
        $class = static::class;

        static::$traitInitializers[$class] = [];

        $traits = static::parentBootTraits();


        foreach ($traits as $trait) {
            if (method_exists($class, $method = 'initialize' . class_basename($trait))) {
                static::$traitInitializers[$class][] = $method;

                static::$traitInitializers[$class] = \array_unique(
                    static::$traitInitializers[$class]
                );
            }
        }

        return $traits;
    }

    /**
     * Initialize any initializable traits on the model.
     *
     * @return void
     */
    protected function initializeTraits(): void
    {
        foreach (static::$traitInitializers[static::class] as $method) {
            $this->{$method}();
        }
    }

    protected function bootIfNotBooted(): void
    {
        $this->parentBootIfNotBooted();

        $this->initializeTraits();
    }
}
