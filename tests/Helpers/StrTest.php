<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

use Php\Support\Helpers\Str;
use Php\Support\Helpers\URLify;
use PHPUnit\Framework\TestCase;

/**
 * Class StrTest
 */
final class StrTest extends TestCase
{
    use HasReflection;

    public function providerDataSnake(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '    ',
                '',
            ],
            [
                'test',
                'test',
            ],
            [
                'testCase',
                'test_case',
            ],
            [
                'TestCase',
                'test_case',
            ],
            [
                'Test Case',
                'test_case',
            ],
            [
                'Ta C',
                'ta_c',
            ],
            [
                'T Ca',
                't_ca',
            ],
            [
                'Test   Case',
                'test_case',
            ],
            [
                'test_case',
                'test_case',
            ],
            [
                'Test   test_case',
                'test_test_case',
            ],
            [
                'test test',
                'test_test',
            ],
            [
                'test TEST 111',
                'test_test_111',
            ],
            [
                'TEST TEST TEST',
                'test_test_test',
            ],
            [
                '123 313 212',
                '123_313_212',
            ],
            [
                'testTestGrab12',
                'test_test_grab_12',
            ],
            [
                '  test Test grab12  ',
                'test_test_grab_12',
            ],
            [
                'AAbbb',
                'a_abbb',
            ],
            [
                'userID',
                'user_id',
            ],
            [
                'JSONData',
                'json_data',
            ],
            [
                'AnyKind of_string',
                'any_kind_of_string',
            ],
        ];
    }

    /**
     * @dataProvider providerDataSnake
     *
     * @param $exp
     * @param $str
     */
    public function testSnake($str, $exp): void
    {
        $result = Str::toSnake($str);

        static::assertEquals($exp, $result);
    }

    public function providerToScreamingSnake(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '    ',
                '',
            ],
            [
                'test',
                'TEST',
            ],
            [
                'testCase',
                'TEST_CASE',
            ],
            [
                'TestCase',
                'TEST_CASE',
            ],
            [
                'Test Case',
                'TEST_CASE',
            ],
            [
                'Ta C',
                'TA_C',
            ],
            [
                'T Ca',
                'T_CA',
            ],
            [
                'Test   Case',
                'TEST_CASE',
            ],
            [
                'test_case',
                'TEST_CASE',
            ],
            [
                'Test   test_case',
                'TEST_TEST_CASE',
            ],
            [
                'test test',
                'TEST_TEST',
            ],
            [
                'test TEST 111',
                'TEST_TEST_111',
            ],
            [
                'TEST TEST TEST',
                'TEST_TEST_TEST',
            ],
            [
                '123 313 212',
                '123_313_212',
            ],
            [
                'testTestGrab12',
                'TEST_TEST_GRAB_12',
            ],
            [
                '  test Test grab12  ',
                'TEST_TEST_GRAB_12',
            ],
            [
                'AAbbb',
                'A_ABBB',
            ],
            [
                'userID',
                'USER_ID',
            ],
            [
                'JSONData',
                'JSON_DATA',
            ],
            [
                'AnyKind of_string',
                'ANY_KIND_OF_STRING',
            ],
        ];
    }

    /**
     * @dataProvider providerToScreamingSnake
     *
     * @param $exp
     * @param $str
     */
    public function testToScreamingSnake($str, $exp): void
    {
        $result = Str::toScreamingSnake($str);

        static::assertEquals($exp, $result);
    }

    public function providerDataKebab(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '    ',
                '',
            ],
            [
                'test',
                'test',
            ],
            [
                'testCase',
                'test-case',
            ],
            [
                'TestCase',
                'test-case',
            ],
            [
                'Test Case',
                'test-case',
            ],
            [
                'Ta C',
                'ta-c',
            ],
            [
                'T Ca',
                't-ca',
            ],
            [
                'Test   Case',
                'test-case',
            ],
            [
                'test_case',
                'test-case',
            ],
            [
                'Test   test_case',
                'test-test-case',
            ],
            [
                'test test',
                'test-test',
            ],
            [
                'test TEST 111',
                'test-test-111',
            ],
            [
                'TEST TEST TEST',
                'test-test-test',
            ],
            [
                '123 313 212',
                '123-313-212',
            ],
            [
                'testTestGrab12',
                'test-test-grab-12',
            ],
            [
                '  test Test grab12  ',
                'test-test-grab-12',
            ],
            [
                'AAbbb',
                'a-abbb',
            ],
            [
                'userID',
                'user-id',
            ],
            [
                'JSONData',
                'json-data',
            ],
            [
                'AnyKind of_string',
                'any-kind-of-string',
            ],
        ];
    }

    /**
     * @dataProvider providerDataKebab
     *
     * @param $exp
     * @param $str
     */
    public function testToKebab($str, $exp): void
    {
        $result = Str::toKebab($str);

        static::assertEquals($exp, $result);
    }


    public function providerCamel(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '    ',
                '',
            ],
            [
                'test_case',
                'TestCase',
            ],
            [
                'testCase',
                'TestCase',
            ],
            [
                'TestCase',
                'TestCase',
            ],
            [
                ' test  case ',
                'TestCase',
            ],
            [
                ' many_many_words',
                'ManyManyWords',
            ],
            [
                'AnyKind of_string',
                'AnyKindOfString',
            ],
            [
                'odd-fix',
                'OddFix',
            ],
            [
                'numbers2And55with000',
                'Numbers2And55With000',
            ],
            [
                'numbe-55w_it 000',
                'Numbe55wIt000',
            ],
            [
                '-55w_it 000',
                '55wIt000',
            ],
            [
                '55w_it 000',
                '55wIt000',
            ],
        ];
    }

    /**
     * @dataProvider providerCamel
     *
     * @param $exp
     * @param $str
     */
    public function testToCamel($str, $exp): void
    {
        $result = Str::toCamel($str);

        static::assertEquals($exp, $result);
    }

    public function providerLowerCamel(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                '    ',
                '',
            ],
            [
                'test_case',
                'testCase',
            ],
            [
                'testCase',
                'testCase',
            ],
            [
                'TestCase',
                'testCase',
            ],
            [
                ' test  case ',
                'testCase',
            ],
            [
                ' many_many_words',
                'manyManyWords',
            ],
            [
                'AnyKind of_string',
                'anyKindOfString',
            ],
            [
                'odd-fix',
                'oddFix',
            ],
            [
                'numbers2And55with000',
                'numbers2And55With000',
            ],
            [
                'numbe-55w_it 000',
                'numbe55wIt000',
            ],
            [
                '-55w_it 000',
                '55wIt000',
            ],
            [
                '55w_it 000',
                '55wIt000',
            ],
        ];
    }

    /**
     * @dataProvider providerLowerCamel
     *
     * @param $exp
     * @param $str
     */
    public function testToLowerCamel($str, $exp): void
    {
        $result = Str::toLowerCamel($str);

        static::assertEquals($exp, $result);
    }

    /**
     * @param $exp
     * @param $str
     */
    public function testToCache(): void
    {
        $result = Str::toSnake(' ets_Case12');
        static::assertEquals('ets_case_12', $result);

        $result = Str::toSnake('ets_Case  12');
        static::assertEquals('ets_case_12', $result);

        $result = Str::toSnake('  ets_Case 12 ');
        static::assertEquals('ets_case_12', $result);
    }


    /**
     * @return array
     */
    public function dataReplaceStrTo(): array
    {
        return [
            [
                '1234567890',
                1,
                1,
                '*',
                '1********0',
            ],
            [
                '1234567890',
                5,
                3,
                '*',
                '12345**890',
            ],
            [
                '1234567890',
                3,
                2,
                '(*)',
                '123(*)(*)(*)(*)(*)90',
            ],
            [
                '1234567890',
                5,
                5,
                '*',
                '1234567890',
            ],
            [
                'eqwopcsvbamx',
                5,
                4,
                '*',
                'eqwop***bamx',
            ],
            [
                'карточка',
                3,
                3,
                '*',
                'кар**чка',
            ],
            [
                'карточка',
                -2,
                3,
                '*',
                '*****чка',
            ],
            [
                'карточка',
                0,
                0,
                '*',
                '********',
            ],
            [
                'карточка',
                3,
                -3,
                '*',
                'кар*****',
            ],
            [
                'карточка',
                -22,
                -3,
                '*',
                '********',
            ],
            [
                'карточка',
                3,
                20,
                '*',
                'карточка',
            ],
            [
                'карточка',
                32,
                3,
                '*',
                'карточка',
            ],
            [
                'карточка',
                32,
                20,
                '*',
                'карточка',
            ],
        ];
    }

    /**
     * @dataProvider  dataReplaceStrTo
     *
     * @param string $val
     * @param int $fromStart
     * @param int $fromEnd
     * @param string $to
     * @param string $exp
     */
    public function testReplaceStrTo(string $val, int $fromStart, int $fromEnd, string $to, string $exp): void
    {
        $result = Str::replaceStrTo($val, $fromStart, $fromEnd, $to);
        static::assertEquals($exp, $result);
    }

    /**
     * @return array
     */
    public function dataReplaceByTemplate(): array
    {
        return [
            [
                'text {{%TOKEN%}} value',
                ['{{%TOKEN%}}' => 'token'],
                'text token value',
            ],
            [
                '"{{%KEY%}}-{{%TOKEN%}}" - test',
                [
                    '{{%KEY%}}'   => 'key',
                    '{{%TOKEN%}}' => 'token',
                ],
                '"key-token" - test',
            ],
            [
                'sdasdas',
                [
                    '{{%KEY%}}'   => 'key',
                    '{{%TOKEN%}}' => 'token',
                ],
                'sdasdas',
            ],
            [
                'sdaas',
                [],
                'sdaas',
            ],
        ];
    }

    /**
     * @dataProvider  dataReplaceByTemplate
     *
     * @param string $str
     * @param array $replaced
     * @param string $exp
     */
    public function testReplaceByTemplate(string $str, array $replaced, string $exp): void
    {
        $result = Str::replaceByTemplate($str, $replaced);
        static::assertEquals($exp, $result);
    }

    public function dataRegExps(): array
    {
        return [
            ['/^(\d+)$/', true],
            ['/([A-Z])\w+/', true],
            ['/\{(?<name>[\w]+?)(:(?<type>[\\\$^()+\w]+?))?}/', true],

            ['^(\d+)$', false],
            ['\d+)$', false],
            ['', false],
            ['test', false],
            ['/\{(?<name>[\w]+?)(:(?<type>[\\\$^()+\w]+?)?}/', false],
        ];
    }

    /**
     * @dataProvider dataRegExps
     *
     * @param string $regexp
     * @param bool $result
     */
    public function testIsRegExp(string $regexp, bool $result): void
    {
        self::assertEquals($result, Str::isRegExp($regexp));
    }


    /**
     * @test
     */
    public function truncate(): void
    {
        self::assertEquals(
            'The quick brown fox...',
            Str::truncate('The quick brown fox jumps over the lazy dog', 24)
        );
        self::assertEquals(
            'The quick brown fox>',
            Str::truncate('The quick brown fox jumps over the lazy dog', 24, '>')
        );
        self::assertEquals(
            'The quick brown fox jumps over the lazy dog',
            Str::truncate('The quick brown fox jumps over the lazy dog', 55)
        );
        self::assertEquals('Th...', Str::truncate('The quick brown fox jumps over the lazy dog', 2));
        self::assertEquals('The...', Str::truncate('The quick brown fox jumps over the lazy dog', 3));
        self::assertEquals('The...', Str::truncate('The quick brown fox jumps over the lazy dog', 7));
    }

    /**
     * @test
     */
    public function seemsUTF8(): void
    {
        // Test a valid UTF-8 sequence: "ÜTF-8 Fµñ".
        $validUTF8 = "\xC3\x9CTF-8 F\xC2\xB5\xC3\xB1";
        self::assertTrue(Str::seemsUTF8($validUTF8));

        self::assertTrue(
            Str::seemsUTF8("\xEF\xBF\xBD this has \xEF\xBF\xBD\xEF\xBF\xBD some invalid UTF-8 \xEF\xBF\xBD")
        );

        // Test invalid UTF-8 sequences
        $invalidUTF8 = "\xc3 this has \xe6\x9d some invalid UTF-8 \xe6";
        self::assertFalse(Str::seemsUTF8($invalidUTF8));

        // And test some plain ASCII
        self::assertTrue(Str::seemsUTF8('The quick brown fox jumps over the lazy dog'));

        // Test an invalid non-UTF-8 string.
        if (function_exists('mb_convert_encoding')) {
            mb_internal_encoding('UTF-8');
            // Converts the 'ç' UTF-8 character to UCS-2LE
            $utf8Char = pack('n', 50087);
            $ucsChar  = mb_convert_encoding($utf8Char, 'UCS-2LE', 'UTF-8');

            self::assertEquals(
                $utf8Char,
                'ç',
                'This PHP system\'s internal character set is not properly set as UTF-8.'
            );
            self::assertEquals($utf8Char, pack('n', 50087), 'Something is wrong with your ICU unicode library.');

            // Test for not UTF-8.
            self::assertFalse(Str::seemsUTF8($ucsChar));

            // Test the worker method.
            $method = self::setMethodAccessible(URLify::class, 'seemsUTF8Regex');
            self::assertFalse(
                $method->invoke(null, $invalidUTF8),
                self::class . '::seemsUTF8Regex did not properly detect invalid UTF-8.'
            );
            self::assertTrue(
                $method->invoke(null, $validUTF8),
                self::class . '::seemsUTF8Regex did not properly detect valid UTF-8.'
            );
        }
    }

    /**
     * @test
     */
    public function slugify(): void
    {
        $this->assertEquals('a-simple-title', Str::slugify('A simple title'));
        $this->assertEquals('this-post-it-has-a-dash', Str::slugify('This post -- it has a dash'));
        $this->assertEquals('123-1251251', Str::slugify('123----1251251'));
        $this->assertEquals('one23-1251251', Str::slugify('123----1251251', '-', true));

        $this->assertEquals('a-simple-title', Str::slugify('A simple title', '-'));
        $this->assertEquals('this-post-it-has-a-dash', Str::slugify('This post -- it has a dash', '-'));
        $this->assertEquals('123-1251251', Str::slugify('123----1251251', '-'));
        $this->assertEquals('one23-1251251', Str::slugify('123----1251251', '-', true));

        $this->assertEquals('a_simple_title', Str::slugify('A simple title', '_'));
        $this->assertEquals('this_post_it_has_a_dash', Str::slugify('This post -- it has a dash', '_'));
        $this->assertEquals('123_1251251', Str::slugify('123----1251251', '_'));
        $this->assertEquals('one23_1251251', Str::slugify('123----1251251', '_', true));

        // Blank separator test
        $this->assertEquals('asimpletitle', Str::slugify('A simple title', ''));
        $this->assertEquals('thispostithasadash', Str::slugify('This post -- it has a dash', ''));
        $this->assertEquals('1231251251', Str::slugify('123----1251251', ''));
        $this->assertEquals('one231251251', Str::slugify('123----1251251', '', true));
    }

}
