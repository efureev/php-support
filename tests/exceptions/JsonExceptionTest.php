<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use Php\Support\Exceptions\JsonException;
use PHPUnit\Framework\TestCase;

/**
 * Class JsonExceptionTest
 */
final class JsonExceptionTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testEmptyThrow(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Unknown JSON encoding/decoding error');
        $this->expectExceptionCode(JsonException::UNKNOWN_ERROR);

        throw new JsonException();
    }

    /**
     * @throws JsonException
     */
    public function testThrowError4(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Syntax error.');

        throw new JsonException(JSON_ERROR_SYNTAX);
    }

    /**
     * @throws JsonException
     */
    public function testThrowByCode(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Syntax error.');

        throw JsonException::byCode(JSON_ERROR_SYNTAX);
    }
}
