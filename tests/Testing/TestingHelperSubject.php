<?php

declare(strict_types=1);

namespace Php\Support\Tests\Testing;

/**
 * Fixture with members of every visibility for TestingHelperTest.
 */
class TestingHelperSubject
{
    public string $openValue = 'public';

    protected string $guardedValue = 'protected';

    private string $secretValue = 'private';

    protected function hidden(string $suffix): string
    {
        return 'protected:' . $suffix;
    }

    protected static function hiddenStatic(): string
    {
        return 'static-hidden';
    }

    private function secretNumber(): int
    {
        return 42;
    }
}
