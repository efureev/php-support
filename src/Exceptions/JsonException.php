<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class JsonException
 *
 * @package Php\Support\Exceptions
 */
final class JsonException extends Exception
{
    /**
     * List of JSON Error messages assigned to constant names for better handling of version differences
     */
    public const ERRORS_MESSAGES = [
        JSON_ERROR_SYNTAX => 'Syntax error.',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
        JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
        JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
        JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
        JSON_ERROR_INVALID_PROPERTY_NAME => 'A property name that cannot be encoded was given',
        JSON_ERROR_UTF16 => 'Malformed UTF-16 characters, possibly incorrectly encoded',
    ];

    /** @var int Unknown error */
    public const UNKNOWN_ERROR = 0;

    /**
     * @param string|null $message
     * @param int $code
     */
    public function __construct($code = self::UNKNOWN_ERROR, ?string $message = 'Unknown JSON encoding/decoding error')
    {
        if ($code) {
            $message = self::ERRORS_MESSAGES[$code] ?? null;
        }

        parent::__construct($message, $code);
    }

    /**
     * @param int $code
     *
     * @return JsonException
     */
    public static function byCode(int $code): self
    {
        return new self($code, self::ERRORS_MESSAGES[$code] ?? null);
    }
}
