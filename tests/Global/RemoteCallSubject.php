<?php

declare(strict_types=1);

namespace Php\Support\Tests\Global;

/**
 * Fixture for RemoteCallTest.
 */
class RemoteCallSubject
{
    public string $title = 'a title';

    protected string $hidden = 'hidden';

    public static function staticGreet(string $suffix = ''): string
    {
        return 'static:' . $suffix;
    }

    public function greet(string $suffix = ''): string
    {
        return 'instance:' . $suffix;
    }

    public function getName(): string
    {
        return 'name';
    }

    public function setName(string $value): void
    {
    }
}
