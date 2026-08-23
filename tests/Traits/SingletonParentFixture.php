<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Traits\Singleton;

/**
 * Fixture for SingletonTest.
 */
class SingletonParentFixture
{
    use Singleton;

    protected $username;
}
