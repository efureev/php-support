<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

use Php\Support\Helpers\B64;
use PHPUnit\Framework\TestCase;

/**
 * Class B64Test
 */
final class B64Test extends TestCase
{
    private static $list = [
        'url'                             => 'dXJs',
        '1'                               => 'MQ==',
        'null'                            => 'bnVsbA==',
        '0'                               => 'MA==',
        'https://github.com/'             => 'aHR0cHM6Ly9naXRodWIuY29tLw==',
        'http://кремль.рф'                => 'aHR0cDovL9C60YDQtdC80LvRjC7RgNGE',
        'https://github.com/?+%'          => 'aHR0cHM6Ly9naXRodWIuY29tLz8rJQ==',
        '(https|http)?_-'                 => 'KGh0dHBzfGh0dHApP18t',
        '12Кириллик'                      => 'MTLQmtC40YDQuNC70LvQuNC6',
        "12Кир\nиллик\nen"                => 'MTLQmtC40YAK0LjQu9C70LjQugplbg==',
        "12Кир\tиллик\ten"                => 'MTLQmtC40YAJ0LjQu9C70LjQugllbg==',
        "'πάντα χωρεῖ καὶ οὐδὲν μένει …'" =>
            'J8+AzqzOvc+EzrEgz4fPic+BzrXhv5YgzrrOseG9tiDOv+G9kM604b2yzr0gzrzOrc69zrXOuSDigKYn',
        '🤪 🤪 😈'                           => '8J+kqiDwn6SqIPCfmIg=',
    ];

    private static $emptyList = ['' => ''];

    public function testEncode(): void
    {
        foreach (self::$list as $str => $decodeStr) {
            $h = B64::encode((string)$str);
            static::assertEquals($decodeStr, $h);
        }

        foreach (self::$emptyList as $str => $decodeStr) {
            $h = B64::encode((string)$str);
            static::assertEquals($decodeStr, $h);
        }
    }

    public function testDecode(): void
    {
        foreach (array_flip(self::$list) as $str => $decodeStr) {
            $h = B64::decode($str);
            static::assertEquals((string)$decodeStr, $h);
        }
    }

    public function testFull(): void
    {
        for ($i = 0; $i < 1000; $i++) {
            $data = \openssl_random_pseudo_bytes(\mt_rand(12, 24));

            static::assertEquals($data, B64::decode(B64::encode($data)));
            static::assertEquals($data, B64::decodeSafe(B64::encodeSafe($data)));
        }
    }

    public function testUrlSafeAlphabetIsRfc4648(): void
    {
        $binary = "\xfb\xff\xfe\x01\x02";

        // interoperable with atob/urlsafe_b64decode/base64.RawURLEncoding
        static::assertSame(
            rtrim(strtr(base64_encode($binary), '+/', '-_'), '='),
            B64::encodeSafe($binary)
        );
        static::assertStringNotContainsString('~', B64::encodeSafe($binary));
        static::assertStringNotContainsString('=', B64::encodeSafe($binary));
    }

    public function testUrlSafeRoundTrip(): void
    {
        foreach (["\xfb\xff\xfe", 'a', 'ab', 'abc', 'Привет, мир!', ''] as $value) {
            static::assertSame($value, B64::decodeSafe(B64::encodeSafe($value)), 'round trip');
        }
    }

    public function testUrlSafeStillReadsTheLegacyTildePadding(): void
    {
        $legacy = strtr(base64_encode('ab'), '+/=', '-_~');

        static::assertSame('ab', B64::decodeSafe($legacy));
    }

    public function testDecodeIsStrictByDefault(): void
    {
        // used to return '' for garbage, indistinguishable from decoding an empty string
        static::assertNull(B64::decode('!!!!'));
        static::assertSame('', B64::decode(''));
        static::assertNull(B64::decodeSafe('!!!!!'));
        static::assertSame('ab', B64::decode(base64_encode('ab')));
    }

    public function testDecodeOrThrow(): void
    {
        static::assertSame('ab', B64::decodeOrThrow(base64_encode('ab')));

        $this->expectException(\Php\Support\Exceptions\InvalidParamException::class);
        B64::decodeOrThrow('!!!!');
    }
}
