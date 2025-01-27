<?php

declare(strict_types=1);

namespace Php\Support\Traits;

use Php\Support\Exceptions\Exception;

/**
 * Trait Singleton
 */
trait Singleton
{
    /**
     * @var array<class-string, Singleton>
     */
    protected static array $instances = [];

    /**
     * prevent the creation of an object through the new operator
     */
    protected function __construct()
    {
    }

    public static function getInstance(): self
    {
        $cls = static::class;
        if (!isset(static::$instances[$cls])) {
            static::$instances[$cls] = new static();
        }

        return static::$instances[$cls];
    }

    /**
     * Singletons should not be recoverable from strings
     *
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize a singleton.');
    }

    /**
     * Singletons should not be cloned
     */
    protected function __clone()
    {
    }
}
