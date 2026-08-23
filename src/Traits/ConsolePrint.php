<?php

declare(strict_types=1);

namespace Php\Support\Traits;

/**
 * Trait ConsolePrint
 * @package Php\Support\Traits
 */
trait ConsolePrint
{
    /** @var array<string, resource> */
    private static array $consoleStreams = [];

    public function print(mixed $msg, bool $newLine = true): void
    {
        fwrite(self::consoleStream('stdout'), print_r($msg, true) . ($newLine ? PHP_EOL : ''));
    }

    /**
     * @codeCoverageIgnore
     */
    public function printError(mixed $msg, bool $newLine = true): void
    {
        fwrite(self::consoleStream('stderr'), print_r($msg, true) . ($newLine ? PHP_EOL : ''));
    }

    /**
     * STDOUT/STDERR only exist under the CLI SAPI, so fall back to the php:// wrappers elsewhere.
     * The constants keep priority so that stream filters attached to them still apply.
     *
     * @return resource
     */
    private static function consoleStream(string $target)
    {
        if ($target === 'stderr' && defined('STDERR')) {
            return STDERR;
        }

        if ($target === 'stdout' && defined('STDOUT')) {
            return STDOUT;
        }

        return self::$consoleStreams[$target] ??= fopen("php://$target", 'wb');
    }
}
