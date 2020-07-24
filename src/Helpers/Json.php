<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use Php\Support\Exceptions\JsonException;

use function json_decode;
use function json_encode;

/**
 * Class Json
 * @package Php\Support\Helpers
 */
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
     * @throws JsonException
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
     * @param int $depth
     *
     * @return string|null
     * @throws JsonException
     */
    public static function encode($value, $options = 320, int $depth = 512): ?string
    {
        $value = Arr::dataToArray($value);
        set_error_handler(
            static function () {
                static::handleJsonError(JSON_ERROR_SYNTAX);
            },
            E_WARNING
        );
        $json = json_encode($value, $options, $depth);
        restore_error_handler();
        static::handleJsonError(json_last_error());

        return $json ?: null;
    }

    /**
     * @param int $lastError
     *
     * @throws JsonException
     */
    protected static function handleJsonError($lastError): void
    {
        if ($lastError === JSON_ERROR_NONE) {
            return;
        }

        throw JsonException::byCode($lastError);
    }

    /**
     * Decodes the given JSON string into a PHP data structure.
     *
     * @param null|string $json the JSON string to be decoded
     * @param bool $asArray whether to return objects in terms of associative arrays.
     * @param int $options
     * @param int $depth
     *
     * @return mixed|null
     * @throws JsonException
     */
    public static function decode(?string $json, $asArray = true, int $options = 0, int $depth = 512)
    {
        if ($json === null || $json === '') {
            return null;
        }
        $decode = json_decode($json, $asArray, $depth, $options);

        static::handleJsonError(json_last_error());

        return $decode;
    }
}
