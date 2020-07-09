<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait ConsolePrint
 * @package Php\Support\Traits
 */
trait ConsolePrint
{
    /**
     * @param mixed $msg
     * @param bool $newLine
     */
    public function print($msg, bool $newLine = true): void
    {
        fwrite(STDOUT, print_r($msg, true) . ($newLine ? PHP_EOL : ''));
    }

    /**
     * @param mixed $msg
     * @param bool $newLine
     * @codeCoverageIgnore
     */
    public function printError($msg, bool $newLine = true): void
    {
        fwrite(STDERR, print_r($msg, true) . ($newLine ? PHP_EOL : ''));
    }
}
