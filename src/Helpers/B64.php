<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use Php\Support\Exceptions\InvalidParamException;

/**
 * Class B64
 *
 * @package Php\Support\Helpers
 */
class B64
{
    /**
     * Encodes the supplied data to Base64
     */
    public static function encode(string $data): string
    {
        return \base64_encode($data);
    }

    /**
     * Decodes the supplied data from Base64.
     *
     * @param string $data
     * @param bool $strict Reject characters outside the alphabet instead of ignoring them.
     *                     Non-strict decoding turns any garbage into an empty string, which is
     *                     indistinguishable from decoding an empty input.
     *
     * @return string|null `null` when the input is not valid Base64
     */
    public static function decode(string $data, bool $strict = true): ?string
    {
        $decoded = \base64_decode($data, $strict);

        return $decoded === false ? null : $decoded;
    }

    /**
     * Encodes the supplied data to the URL-safe alphabet of RFC 4648 §5.
     *
     * `+` becomes `-`, `/` becomes `_`, and the padding is stripped. Output is interoperable with
     * JavaScript, Python's `urlsafe_b64decode` and Go's `base64.RawURLEncoding`.
     */
    public static function encodeSafe(string $data): string
    {
        return \rtrim(\strtr(self::encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodes the supplied data from the URL-safe alphabet of RFC 4648 §5.
     *
     * Padding is optional. The legacy `~` this package used for `=` until 6.0 is still accepted,
     * so URLs minted by an older version keep working.
     *
     * @return string|null `null` when the input is not valid URL-safe Base64
     */
    public static function decodeSafe(string $data, bool $strict = true): ?string
    {
        // '~' was this package's own non-standard stand-in for the padding character
        $data = \strtr($data, '-_~', '+/=');

        $remainder = \strlen($data) % 4;

        if ($remainder !== 0) {
            if ($remainder === 1) {
                return null;
            }

            $data .= \str_repeat('=', 4 - $remainder);
        }

        return self::decode($data, $strict);
    }

    /**
     * Decodes the supplied data from Base64 or throws.
     *
     * @throws InvalidParamException when the input is not valid Base64
     */
    public static function decodeOrThrow(string $data): string
    {
        return self::decode($data) ?? throw new InvalidParamException('Value is not valid Base64', 'data');
    }
}
