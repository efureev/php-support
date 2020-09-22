<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use Php\Support\Traits\TraitBooter;
use Php\Support\Traits\TraitInitializer;
use PHPUnit\Framework\TestCase;

final class TraitBooterTest extends TestCase
{
    public function testBootTrait(): void
    {
        self::assertEquals('class', BootClass::$type);
        $class = new BootClass();
        self::assertEquals('trait', $class::$type);
    }

    public function testInitTrait(): void
    {
        $class = new InitClass();
        self::assertEquals('load initialize from InitTrait', $class->title);
    }
}

class BootClass
{
    use TraitBooter;
    use BootTrait;

    public static $type = 'class';

    public function __construct()
    {
        $this->bootIfNotBooted();
        //        $this->initializeTraits();

    }
}

class InitClass
{
    use TraitInitializer;
    use InitTrait;

    public $title = '';

    public function __construct()
    {
        $this->bootIfNotBooted();
    }
}

trait BootTrait
{
    public static function bootBootTrait()
    {
        static::$type = 'trait';
    }
}

trait InitTrait
{
    public function initializeInitTrait()
    {
        $this->title = 'load initialize from InitTrait';
    }
}
