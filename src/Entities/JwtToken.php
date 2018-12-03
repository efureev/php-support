<?php

namespace Php\Support\Entities;

/**
 * Class JwtToken
 *
 * @package Php\Support\Entities
 */
class JwtToken
{
    /**
     * The token headers
     *
     * @var array
     */
    private $headers;

    /**
     * The token claim set
     *
     * @var array
     */
    private $claims;

    /**
     * The token signature hash
     *
     * @var string|null
     */
    private $signature;

    /**
     * The encoded data
     *
     * @var array
     */
    private $payload;

    /**
     * Initializes the object
     *
     * @param array  $headers
     * @param array  $claims
     * @param array  $payload
     * @param string $signature
     */
    public function __construct(
        array $headers = ['alg' => 'none'],
        array $claims = [],
        ?string $signature = null,
        array $payload = ['', '']
    ) {
        $this->headers = $headers;
        $this->claims = $claims;
        $this->signature = $signature;
        $this->payload = $payload;
    }

    /**
     * Returns the token headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Returns if the header is configured
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasHeader(string $name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * Returns the value of a token header
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function getHeader(string $name, $default = null)
    {
        if ($this->hasHeader($name)) {
            return $this->getHeaderValue($name);
        }

        if ($default === null) {
            throw new \OutOfBoundsException('Requested header is not configured');
        }

        return $default;
    }

    /**
     * Returns the value stored in header
     *
     * @param string $name
     *
     * @return string
     */
    private function getHeaderValue(string $name): string
    {
        return $this->headers[ $name ];
    }

    /**
     * Returns the token claim set
     *
     * @return array
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * Returns if the claim is configured
     *
     * @param string $name
     *
     * @return boolean
     */
    public function hasClaim(string $name): bool
    {
        return array_key_exists($name, $this->claims);
    }

    /**
     * Returns the value of a token claim
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function getClaim(string $name, $default = null)
    {
        if ($this->hasClaim($name)) {
            return $this->claims[ $name ];
        }

        if ($default === null) {
            throw new \OutOfBoundsException('Requested claim is not configured');
        }

        return $default;
    }

    /**
     * Determine if the token is expired.
     *
     * @param \DateTimeInterface|null $now
     *
     * @return bool
     * @throws \Exception
     */
    public function isExpired(\DateTimeInterface $now = null)
    {
        $exp = $this->getClaim('exp', false);

        if ($exp === false) {
            return false;
        }

        $now = $now ?: new \DateTime();

        $expiresAt = new \DateTime();
        $expiresAt->setTimestamp($exp);

        return $now > $expiresAt;
    }

    /**
     * Returns the token payload
     *
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload[0] . '.' . $this->payload[1];
    }

    /**
     * Returns an encoded representation of the token
     *
     * @return string
     */
    public function __toString()
    {
        $data = implode('.', $this->payload);

        if ($this->signature === null) {
            $data .= '.';
        }

        return $data;
    }
}
