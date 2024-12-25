<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait ConsolePrint
 * @package Php\Support\Traits
 */
trait ConsolePrint
{
    public function print(mixed $msg, bool $newLine = true): void
    {
        fwrite(STDOUT, print_r($msg, true) . ($newLine ? PHP_EOL : ''));
    }

    /**
     * @codeCoverageIgnore
     */
    public function printError(mixed $msg, bool $newLine = true): void
    {
        fwrite(STDERR, print_r($msg, true) . ($newLine ? PHP_EOL : ''));
    }
}
