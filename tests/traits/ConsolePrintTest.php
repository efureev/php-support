<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class ConsolePrintTest
 */
final class ConsolePrintTest extends TestCase
{
    public function testStdOut(): void
    {
        stream_filter_register('intercept', '\Php\Support\Tests\InterceptFilter');

        stream_filter_append(STDOUT, 'intercept');

        $str1 = 'Test message';
        $this->cls()->print($str1);
        $this->assertEquals($str1 . PHP_EOL, InterceptFilter::$cache);

        $this->cls()->print($str1, false);
        $this->assertEquals($str1, InterceptFilter::$cache);

        $array = [
            'key'   => 'value',
            'int'   => 323,
            'float' => 3.12,
        ];

        $this->cls()->print($array, false);
        $this->assertEquals(print_r($array, true), InterceptFilter::$cache);

        $this->cls()->print($array);
        $this->assertEquals(print_r($array, true) . PHP_EOL, InterceptFilter::$cache);
    }

    public function testErrOut(): void
    {
        stream_filter_register('intercept', '\Php\Support\Tests\InterceptFilter');

        stream_filter_append(STDERR, 'intercept');

        $str1 = 'Error message';
        $this->cls()->printError($str1);
        $this->assertEquals($str1 . PHP_EOL, InterceptFilter::$cache);

        $this->cls()->printError($str1, false);
        $this->assertEquals($str1, InterceptFilter::$cache);
    }

    private function cls()
    {
        return new class () {
            use \Php\Support\Traits\ConsolePrint;
        };
    }
}
