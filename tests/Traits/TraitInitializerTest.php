<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Traits\TraitInitializer;
use PHPUnit\Framework\TestCase;

final class TraitInitializerTest extends TestCase
{
    public function testInitializerIsCalledOnConstruct(): void
    {
        $object = new TraitInitializerSubject();

        self::assertSame('initialized', $object->marker);
    }

    public function testBootIsCalledOnce(): void
    {
        TraitInitializerSubject::clearBooted();
        TraitInitializerSubject::$bootCount = 0;

        new TraitInitializerSubject();
        new TraitInitializerSubject();

        self::assertSame(1, TraitInitializerSubject::$bootCount);
    }

    public function testInitializerRunsForEachInstance(): void
    {
        $first  = new TraitInitializerSubject();
        $second = new TraitInitializerSubject();

        self::assertSame('initialized', $first->marker);
        self::assertSame('initialized', $second->marker);
    }
}

trait InitializableTrait
{
    public string $marker = '';

    public function initializeInitializableTrait(): void
    {
        $this->marker = 'initialized';
    }
}

trait BootCounterTrait
{
    public static int $bootCount = 0;

    public static function bootBootCounterTrait(): void
    {
        static::$bootCount++;
    }
}

class TraitInitializerSubject
{
    use TraitInitializer;
    use InitializableTrait;
    use BootCounterTrait;

    public function __construct()
    {
        $this->bootIfNotBooted();
    }
}
