<?php
declare(strict_types=1);

use Php\Support\Entities\JwtToken;
use Php\Support\Helpers\JwtParser;
use PHPUnit\Framework\TestCase;

/**
 * Class JwtParserTest
 */
final class JwtParserTest extends TestCase
{

    /**
     * @return string
     */
    private static function getIdToken(): string
    {
        return 'eyJraWQiOiJvY3QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIyIiwiaXNzIjoiaHR0cDovLzE5Mi4xNjguMC4xLyIsIm5pY2tuYW1lIjoiSVZBTk9WIiwiZXhwIjoxNTQzODQ2MDA3LCJpYXQiOjE1NDM4NDU0MDcsImp0aSI6ImQyNzRiNTljLWIzODYtNDQ2MS1hZGM1LWRkNjI2Mzk5ZjBhYiIsInBob25lIjoiNzkxMTEyMDAwMDAiLCJuYW1lIjoi0JjQstCw0L3QvtCyINCY0LLQsNC9INCY0LLQsNC90L7QstC40YciLCJwb3NpdGlvbiI6ItCd0LUg0L3QsNC30L3QsNGH0LXQvdCwIiwiZmFtaWx5X25hbWUiOiLQmNCy0LDQvdC-0LIiLCJwb3NpdGlvbl9pZCI6MCwidXNlcm5hbWUiOiJJVkFOT1YifQ.GFnYCTAznSuRmoKARmKXGf8_iP3VcKtNlyRDD5tq5mI';
    }

    public function testParse()
    {
        $token = JwtParser::parseToken(static::getIdToken());

        $this->assertInstanceOf(JwtToken::class, $token);

        $this->assertEquals('HS256', $token->getHeader('alg'));
        $this->assertEquals(2, $token->getClaim('sub'));
        $this->assertEquals('IVANOV', $token->getClaim('username'));
        $this->assertEquals('Иванов Иван Иванович', $token->getClaim('name'));

    }

}
