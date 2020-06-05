<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

/**
 * Class B64
 *
 * @package Php\Support\Helpers
 */
class B64
{
    /**
     * The last three characters from the alphabet of the standard implementation
     *
     * @var string
     */
    private const LAST_THREE_STANDARD = '+/=';

    /**
     * The last three characters from the alphabet of the URL-safe implementation
     *
     * @var string
     */
    private const LAST_THREE_URL_SAFE = '-_~';

    /**
     * Encodes the supplied data to Base64
     *
     * @param string $data
     *
     * @return string
     */
    public static function encode(string $data): string
    {
        return \base64_encode($data);
    }

    /**
     * Decodes the supplied data from Base64
     *
     * @param string $data
     * @param bool $strict
     *
     * @return string|null
     */
    public static function decode(string $data, bool $strict = false): ?string
    {
        return ($str = \base64_decode($data, $strict)) !== false ? $str : null;
    }

    /**
     * Encodes the supplied data to a URL-safe variant of Base64
     *
     * @param string $data
     *
     * @return string
     */
    public static function encodeSafe(string $data): string
    {
        $encoded = self::encode($data);

        return \strtr(
            $encoded,
            self::LAST_THREE_STANDARD,
            self::LAST_THREE_URL_SAFE
        );
    }

    /**
     * Decodes the supplied data from a URL-safe variant of Base64
     *
     * @param string $data
     *
     * @return string|null
     */
    public static function decodeSafe(string $data): ?string
    {
        $data = \strtr(
            $data,
            self::LAST_THREE_URL_SAFE,
            self::LAST_THREE_STANDARD
        );

        return self::decode($data);
    }
}
