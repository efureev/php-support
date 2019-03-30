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
        return static::encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
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
     *
     * @return string|null
     * @throws JsonException
     */
    public static function encode($value, $options = 320): ?string
    {
//        $value = static::dataToArray($value);
        $value = Arr::dataToArray($value);
        set_error_handler(static function (): void {
            static::handleJsonError(JSON_ERROR_SYNTAX);
        }, E_WARNING);
        $json = json_encode($value, $options);
        restore_error_handler();
        static::handleJsonError(json_last_error());

        return $json ?: null;
    }

    /**
     * Pre-processes the data before sending it to `json_encode()`.
     *
     * @param mixed $data the data to be processed
     *
     * @return mixed the processed data
     */
    /* public static function dataToArray($data)
     {
         if (is_object($data)) {
             if ($data instanceof JsonSerializable) {
                 return static::dataToArray($data->jsonSerialize());
             }
             if ($data instanceof Arrayable) {
                 $data = $data->toArray();
             } else {
                 $result = [];
                 if (is_iterable($data)) {
                     foreach ($data as $name => $value) {
                         $result[$name] = $value;
                     }
                 }
                 $data = $result;
             }
         }

         if (is_array($data)) {
             foreach ($data as $key => $value) {
                 if (is_array($value) || is_object($value)) {
                     $data[$key] = static::dataToArray($value);
                 }
             }
         }

         return $data;
     }*/

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
     *
     * @return mixed|null
     * @throws JsonException
     */
    public static function decode(?string $json, $asArray = true)
    {
        if (null === $json || $json === '') {
            return null;
        }
        $decode = json_decode($json, $asArray);

        static::handleJsonError(json_last_error());

        return $decode;
    }
}
