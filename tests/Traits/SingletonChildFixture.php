<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

/**
 * Fixture inheriting the singleton from its parent.
 */
final class SingletonChildFixture extends SingletonParentFixture
{
    private $password;

    public function __sleep()
    {
        return [
            'username',
            'password',
        ];
    }
}
