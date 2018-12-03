<?php

namespace Php\Support\Helpers;

use Php\Support\Entities\JwtToken;
use Php\Support\Exceptions\InvalidArgumentException;

/**
 * Class Jwt
 *
 * @package Php\Support\Helpers
 */
class JwtParser
{
    /** @var \Closure */
    public $decodeFn;

    /** @var string */
    private $token;

    /**
     * JwtParser constructor.
     *
     * @param string        $token
     * @param null|\Closure $decodeFn
     */
    public function __construct(string $token, $decodeFn = null)
    {
        $this->token = $token;
        $this->decodeFn = $decodeFn ?? \Closure::fromCallable([$this, 'base64UrlDecode']);
    }

    /**
     * @param string $data
     *
     * @return bool|string
     */
    public static function base64UrlDecode(string $data)
    {
        if ($remainder = strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * @param string $json
     *
     * @return mixed
     */
    private static function toArray(string $json)
    {
        return Json::decode($json);
    }

    /**
     * @param string $token
     *
     * @return \Php\Support\Entities\JwtToken
     */
    public static function parseToken(string $token)
    {
        return (new self($token))->parse();
    }

    /**
     * Parse data token
     *
     * @return \Php\Support\Entities\JwtToken
     */
    private function parse()
    {
        $data = $this->splitJwt();

        $header = $this->parseHeader($data[0]);
        $claims = $this->parseClaims($data[1]);
        $signature = $this->parseSignature($header, $data[2]);

        foreach ($claims as $name => $value) {
            if (isset($header[ $name ])) {
                $header[ $name ] = $value;
            }
        }

        if ($signature === null) {
            unset($data[2]);
        }

        return new JwtToken($header, $claims, $signature, $data);
    }

    /**
     * @return array
     */
    private function splitJwt()
    {
        $data = explode('.', $this->token);

        if (count($data) != 3) {
            throw new InvalidArgumentException('The JWT string must have two dots');
        }

        return $data;
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private function decodeFn(string $data): string
    {
        $fn = $this->decodeFn;

        return $fn($data);
    }

    /**
     * @param string $data
     *
     * @return array
     */
    private function parseHeader(string $data)
    {
        $header = static::toArray($this->decodeFn($data));

        if (isset($header['enc'])) {
            throw new InvalidArgumentException('Encryption is not supported yet');
        }

        return $header;
    }

    /**
     * Parses the claim set from a string
     *
     * @param string $data
     *
     * @return array
     */
    private function parseClaims(string $data)
    {
        return static::toArray($this->decodeFn($data));
    }

    /**
     * Returns the signature from given data
     *
     * @param array  $header
     * @param string $data
     *
     * @return string|null
     */
    private function parseSignature(array $header, string $data): ?string
    {
        if ($data === '' || !isset($header['alg']) || $header['alg'] == 'none') {
            return null;
        }

        return $this->decodeFn($data);
    }
}
