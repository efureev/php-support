<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Class ConsolePrint
 */
final class ConsolePrint extends TestCase
{
    public function testStdOut(): void
    {
        stream_filter_register('intercept', 'Intercept');

        stream_filter_append(STDOUT, 'intercept');

        $str1 = 'Test message';
        $this->cls()->print($str1);
        $this->assertEquals($str1 . PHP_EOL, Intercept::$cache);

        $this->cls()->print($str1, false);
        $this->assertEquals($str1, Intercept::$cache);

        $array = [
            'key'   => 'value',
            'int'   => 323,
            'float' => 3.12,
        ];

        $this->cls()->print($array, false);
        $this->assertEquals(print_r($array, true), Intercept::$cache);

        $this->cls()->print($array);
        $this->assertEquals(print_r($array, true) . PHP_EOL, Intercept::$cache);
    }

    public function testErrOut(): void
    {
        stream_filter_register('intercept', 'Intercept');

        stream_filter_append(STDERR, 'intercept');

        $str1 = 'Error message';
        $this->cls()->printError($str1);
        $this->assertEquals($str1 . PHP_EOL, Intercept::$cache);

        $this->cls()->printError($str1, false);
        $this->assertEquals($str1, Intercept::$cache);
    }

    private function cls()
    {
        return new class ()
        {
            use \Php\Support\Traits\ConsolePrint;
        };
    }
}

/**
 * Class Intercept
 */
class Intercept extends \php_user_filter
{
    public static $cache = '';

    /**
     * @param resource $in
     * @param resource $out
     * @param int $consumed
     * @param bool $closing
     *
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            self::$cache = $bucket->data;
            $consumed   += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
