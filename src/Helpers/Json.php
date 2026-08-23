<?php

declare(strict_types=1);

namespace Php\Support\Helpers;

use Php\Support\Exceptions\InvalidValueException;

use function json_decode;
use function json_encode;
use function json_last_error_msg;

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

        return $json === false ? null : $json;
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

    /**
     * Encodes a value into JSON or throws.
     *
     * {@see self::encode()} returns null for every failure, which is indistinguishable from
     * a successful encode of a value that has no JSON representation.
     *
     * @param mixed $value the data to be encoded
     * @param int $options the encoding options
     * @param int<1, max> $depth
     *
     * @throws InvalidValueException when the value cannot be encoded
     */
    public static function encodeOrThrow(mixed $value, int $options = 320, int $depth = 512): string
    {
        $json = static::encode($value, $options, $depth);

        if ($json === null) {
            throw new InvalidValueException('Unable to encode value as JSON: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Decodes a JSON string or throws.
     *
     * {@see self::decode()} returns null both for invalid JSON and for the valid document `null`,
     * so the caller cannot tell a parse failure from a legitimate null.
     *
     * @param null|string $json the JSON string to be decoded
     * @param bool $asArray whether to return objects as associative arrays
     * @param int $options
     * @param int<1, max> $depth
     *
     * @throws InvalidValueException when the string is not valid JSON
     */
    public static function decodeOrThrow(
        ?string $json,
        bool $asArray = true,
        int $options = 0,
        int $depth = 512
    ): mixed {
        if ($json === null || $json === '') {
            throw new InvalidValueException('Unable to decode an empty string as JSON');
        }

        try {
            return json_decode($json, $asArray, $depth, $options | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // rethrow through the package hierarchy so ExceptionInterface still catches it
            throw new InvalidValueException('Unable to decode JSON: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Encodes a value as human-readable JSON.
     *
     * @param mixed $value
     * @param int<1, max> $depth
     */
    public static function prettyPrint(mixed $value, int $depth = 512): ?string
    {
        return static::encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT, $depth);
    }

    /**
     * Checks whether the given string is valid JSON.
     */
    /**
     * @param int<1, max> $depth
     */
    public static function isValid(?string $json, int $depth = 512): bool
    {
        return $json !== null && $json !== '' && json_validate($json, $depth);
    }
}
