<?php

declare(strict_types=1);

namespace Php\Support\Tests\traits;

/**
 * Class InterceptFilter
 */
class InterceptFilter extends \php_user_filter
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

            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
