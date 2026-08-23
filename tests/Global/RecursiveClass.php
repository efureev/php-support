<?php

declare(strict_types=1);

namespace Php\Support\Tests\Global;

use Php\Support\Traits\Maker;

/**
 * Fixture inheriting traits from its parent.
 */
class RecursiveClass extends TraitUsesRecursiveClass
{
    use Maker;
}
