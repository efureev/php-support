<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use function json_decode;
use function json_encode;

class Json
{
    /**
     * Encodes the given value into a JSON string HTML-escaping entities so it is safe to be embedded in HTML code.
     * The method enhances `json_encode()` by supporting JavaScript expressions.
     * Note that data encoded as JSON must be UTF-8 encoded according to the JSON specification.
     * You must ensure strings passed to this method have proper encoding before passing them.
     *
     * @param mixed $value the data to be encoded
     *
     * @return string|null
     */
    public static function htmlEncode($value): ?string
    {
        return static::encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
        );
    }

    /**
     * Encodes the given value into a JSON string.
     * The method enhances `json_encode()` by supporting JavaScript expressions.
     * Note that data encoded as JSON must be UTF-8 encoded according to the JSON specification.
     * You must ensure strings passed to this method have proper encoding before passing them.
     *
     * @param mixed $value the data to be encoded.
     * @param int $options the encoding options. For more details please refer to
     *                       <http://www.php.net/manual/en/function.json-encode.php>. Default is
     *                       `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE`.
     * @param int<1, max> $depth
     *
     * @return string|null
     */
    public static function encode($value, int $options = 320, int $depth = 512): ?string
    {
        $value = Arr::dataToArray($value);

        $json = json_encode($value, $options, $depth);

        return $json ?: null;
    }

    /**
     * Decodes the given JSON string into a PHP data structure.
     *
     * @param null|string $json the JSON string to be decoded
     * @param bool $asArray whether to return objects in terms of associative arrays.
     * @param int $options
     * @param int<1, max> $depth
     *
     * @return mixed|null
     */
    public static function decode(?string $json, bool $asArray = true, int $options = 0, int $depth = 512)
    {
        if ($json === null || $json === '') {
            return null;
        }

        // @see https://www.php.net/manual/en/json.constants.php#constant.json-invalid-utf8-ignore
        $validateOpts = Bit::checkFlag($options, JSON_INVALID_UTF8_IGNORE) ? JSON_INVALID_UTF8_IGNORE : 0;
        if (!json_validate($json, $depth, $validateOpts)) {
            return null;
        }

        return json_decode($json, $asArray, $depth, $options);
    }
}
