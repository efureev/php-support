<?php

namespace Php\Support\Helpers;

use Php\Support\Exceptions\InvalidArgumentException;
use Php\Support\Interfaces\Arrayable;

class Json
{
    /**
     * List of JSON Error messages assigned to constant names for better handling of version differences
     *
     * @var array
     */
    public static $jsonErrorMessages = [
        'JSON_ERROR_DEPTH' => 'The maximum stack depth has been exceeded.',
        'JSON_ERROR_STATE_MISMATCH' => 'Invalid or malformed JSON.',
        'JSON_ERROR_CTRL_CHAR' => 'Control character error, possibly incorrectly encoded.',
        'JSON_ERROR_SYNTAX' => 'Syntax error.',
        'JSON_ERROR_UTF8' => 'Malformed UTF-8 characters, possibly incorrectly encoded.', // PHP 5.3.3
        'JSON_ERROR_RECURSION' => 'One or more recursive references in the value to be encoded.', // PHP 5.5.0
        'JSON_ERROR_INF_OR_NAN' => 'One or more NAN or INF values in the value to be encoded', // PHP 5.5.0
        'JSON_ERROR_UNSUPPORTED_TYPE' => 'A value of a type that cannot be encoded was given', // PHP 5.5.0
    ];


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
     * @return null|string the encoding result.
     * @throws InvalidArgumentException if there is any encoding error.
     */
    public static function encode($value, $options = 320): ?string
    {
        $value = static::dataToArray($value);
        set_error_handler(function () {
            static::handleJsonError(JSON_ERROR_SYNTAX);
        }, E_WARNING);
        $json = \json_encode($value, $options);
        restore_error_handler();
        static::handleJsonError(json_last_error());

        return $json ?: null;
    }

    /**
     * Encodes the given value into a JSON string HTML-escaping entities so it is safe to be embedded in HTML code.
     * The method enhances `json_encode()` by supporting JavaScript expressions.
     * Note that data encoded as JSON must be UTF-8 encoded according to the JSON specification.
     * You must ensure strings passed to this method have proper encoding before passing them.
     *
     * @param mixed $value the data to be encoded
     *
     * @return string|null the encoding result
     * @throws InvalidArgumentException if there is any encoding error
     */
    public static function htmlEncode($value): ?string
    {
        return static::encode($value, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }

    /**
     * Decodes the given JSON string into a PHP data structure.
     *
     * @param null|string $json the JSON string to be decoded
     * @param bool $asArray whether to return objects in terms of associative arrays.
     *
     * @return mixed the PHP data
     * @throws InvalidArgumentException if there is any decoding error
     */
    public static function decode(?string $json, $asArray = true)
    {
        if (is_null($json) || $json === '') {
            return null;
        }
        $decode = \json_decode($json, $asArray);
        static::handleJsonError(json_last_error());

        return $decode;
    }

    /**
     * Handles [[encode()]] and [[decode()]] errors by throwing exceptions with the respective error message.
     *
     * @param int $lastError error code from [json_last_error()](http://php.net/manual/en/function.json-last-error.php).
     *
     * @throws InvalidArgumentException if there is any encoding/decoding error.
     */
    protected static function handleJsonError($lastError)
    {
        if ($lastError === JSON_ERROR_NONE) {
            return;
        }
        $availableErrors = [];
        foreach (static::$jsonErrorMessages as $const => $message) {
            if (defined($const)) {
                $availableErrors[constant($const)] = $message;
            }
        }
        if (isset($availableErrors[$lastError])) {
            throw new InvalidArgumentException($availableErrors[$lastError], $lastError);
        }
        throw new InvalidArgumentException('Unknown JSON encoding/decoding error.');
    }

    /**
     * Pre-processes the data before sending it to `json_encode()`.
     *
     * @param mixed $data the data to be processed
     *
     * @return mixed the processed data
     */
    public static function dataToArray($data)
    {
        if (is_object($data)) {
            if ($data instanceof \JsonSerializable) {
                return static::dataToArray($data->jsonSerialize());
            } elseif ($data instanceof Arrayable) {
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
    }
}
