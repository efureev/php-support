<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Traits\TraitBooter;
use Php\Support\Traits\TraitInitializer;
use Php\Support\Traits\Whener;
use PHPUnit\Framework\TestCase;

final class TraitWhenerTest extends TestCase
{
    public function testBootTrait(): void
    {
        $class = new class {
            use Whener;
        };

        self::assertEquals(1, $class->when(true, fn()=>1));
    }
}