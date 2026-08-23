<?php

declare(strict_types=1);

namespace Php\Support\Tests\Global;

use Php\Support\Traits\Singleton;
use Php\Support\Traits\UseConfigurableStorage;

/**
 * Fixture for the trait-reflection helpers.
 */
class TraitUsesRecursiveClass
{
    use Singleton;
    use UseConfigurableStorage;

    protected $username;
}
