<?php

namespace Tests\Traits;

/**
 * Trait ConsolePrintTrait
 *
 * @package Tests\Traits
 */
trait ConsolePrint
{
    /**
     * @param mixed $msg
     */
    public function print($msg): void
    {
        fwrite(STDOUT, print_r($msg, true) . PHP_EOL);
    }

    /**
     * @param mixed $msg
     */
    public function printError($msg): void
    {
        fwrite(STDERR, print_r($msg, true) . PHP_EOL);
    }
}