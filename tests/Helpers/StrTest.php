<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Helpers\Str;
use Php\Support\Helpers\URLify;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Class StrTest
 */
final class StrTest extends TestCase
{
    use HasReflection;

    public static function providerDataSnake(): array
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

    #[DataProvider('providerDataSnake')]
    public function testSnake($str, $exp): void
    {
        $result = Str::toSnake($str);

        static::assertEquals($exp, $result);
    }

    public static function providerToScreamingSnake(): array
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

    #[DataProvider('providerToScreamingSnake')]
    public function testToScreamingSnake($str, $exp): void
    {
        $result = Str::toScreamingSnake($str);

        static::assertEquals($exp, $result);
    }

    public static function providerDataKebab(): array
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

    #[DataProvider('providerDataKebab')]
    public function testToKebab($str, $exp): void
    {
        $result = Str::toKebab($str);

        static::assertEquals($exp, $result);
    }


    public static function providerCamel(): array
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


    #[DataProvider('providerCamel')]
    public function testToCamel($str, $exp): void
    {
        $result = Str::toCamel($str);

        static::assertEquals($exp, $result);
    }

    public static function providerLowerCamel(): array
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

    #[DataProvider('providerLowerCamel')]
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
    public static function dataReplaceStrTo(): array
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

    #[DataProvider('dataReplaceStrTo')]
    public function testReplaceStrTo(string $val, int $fromStart, int $fromEnd, string $to, string $exp): void
    {
        $result = Str::replaceStrTo($val, $fromStart, $fromEnd, $to);
        static::assertEquals($exp, $result);
    }

    /**
     * @return array
     */
    public static function dataReplaceByTemplate(): array
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


    #[DataProvider('dataReplaceByTemplate')]
    public function testReplaceByTemplate(string $str, array $replaced, string $exp): void
    {
        $result = Str::replaceByTemplate($str, $replaced);
        static::assertEquals($exp, $result);
    }

    public static function dataRegExps(): array
    {
        return [
            [
                '/^(\d+)$/',
                true,
            ],
            [
                '/([A-Z])\w+/',
                true,
            ],
            [
                '/\{(?<name>[\w]+?)(:(?<type>[\\\$^()+\w]+?))?}/',
                true,
            ],

            [
                '^(\d+)$',
                false,
            ],
            [
                '\d+)$',
                false,
            ],
            [
                '',
                false,
            ],
            [
                'test',
                false,
            ],
            [
                '/\{(?<name>[\w]+?)(:(?<type>[\\\$^()+\w]+?)?}/',
                false,
            ],
        ];
    }

    #[DataProvider('dataRegExps')]
    public function testIsRegExp(string $regexp, bool $result): void
    {
        self::assertEquals($result, Str::isRegExp($regexp));
    }


    #[Test]
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

    #[Test]
    public function seemsUTF8(): void
    {
        // Test a valid UTF-8 sequence: "ÜTF-8 Fµñ".
        $validUTF8 = "\xC3\x9CTF-8 F\xC2\xB5\xC3\xB1";
        self::assertTrue(URLify::seemsUTF8($validUTF8));

        self::assertTrue(
            URLify::seemsUTF8("\xEF\xBF\xBD this has \xEF\xBF\xBD\xEF\xBF\xBD some invalid UTF-8 \xEF\xBF\xBD")
        );

        // Test invalid UTF-8 sequences
        $invalidUTF8 = "\xc3 this has \xe6\x9d some invalid UTF-8 \xe6";
        self::assertFalse(URLify::seemsUTF8($invalidUTF8));

        // And test some plain ASCII
        self::assertTrue(URLify::seemsUTF8('The quick brown fox jumps over the lazy dog'));

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
            self::assertFalse(URLify::seemsUTF8($ucsChar));
        }
    }

    #[Test]
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

    #[Test]
    public function trimPrefix(): void
    {
        $this->assertEquals('title', Str::trimPrefix('a-simple:title', 'a-simple:'));
        $this->assertEquals('a-simple:title', Str::trimPrefix('a-simple:title', ''));
        $this->assertEquals('a-simple:title', Str::trimPrefix('a-simple:title', 'asdas'));
        $this->assertEquals('a-simple:title', Str::trimPrefix('a-simple:title', 'a-sdas'));
        $this->assertEquals('', Str::trimPrefix('', 'a-simple:'));
    }

    #[Test]
    public function trimSuffix(): void
    {
        $this->assertEquals('a-simple:', Str::trimSuffix('a-simple:title', 'title'));
        $this->assertEquals('a-simple:title', Str::trimSuffix('a-simple:title', ''));
        $this->assertEquals('a-simple:title', Str::trimSuffix('a-simple:title', 'asdas'));
        $this->assertEquals('a-simple:title', Str::trimSuffix('a-simple:title', 'a-sdas'));
        $this->assertEquals('', Str::trimSuffix('', 'a-simple:'));
    }

    #[Test]
    public function truncateRejectsNonPositiveLength(): void
    {
        $this->expectException(InvalidParamException::class);
        Str::truncate('abc', 0);
    }

    #[Test]
    public function truncateRejectsNegativeLength(): void
    {
        $this->expectException(InvalidParamException::class);
        Str::truncate('abc', -1);
    }

    #[Test]
    public function slugifyWithFormatRejectsBrokenPattern(): void
    {
        $this->expectException(InvalidParamException::class);
        Str::slugifyWithFormat('a/b', '-', 'a/b');
    }

    #[Test]
    public function randomProducesRequestedLength(): void
    {
        self::assertSame(16, strlen(Str::random()));
        self::assertSame(1, strlen(Str::random(1)));
        self::assertSame(64, strlen(Str::random(64)));
        self::assertNotSame(Str::random(32), Str::random(32));
        self::assertMatchesRegularExpression('/^[A-Za-z0-9]+$/', Str::random(40));
    }

    #[Test]
    public function randomRejectsNonPositiveLength(): void
    {
        $this->expectException(InvalidParamException::class);
        Str::random(0);
    }

    #[Test]
    public function mask(): void
    {
        self::assertSame('1234********3456', Str::mask('1234567890123456', '*', 4, 8));
        self::assertSame('sec***', Str::mask('secret', '*', -3));
        self::assertSame('******', Str::mask('secret'));
        self::assertSame('abc', Str::mask('abc', ''));
        self::assertSame('', Str::mask('', '*'));
        self::assertSame('abc', Str::mask('abc', '*', 10));
        // multibyte aware
        self::assertSame('Пр***т', Str::mask('Привет', '*', 2, 3));
    }

    #[Test]
    public function limitCutsWithoutWordAwareness(): void
    {
        self::assertSame('The quick...', Str::limit('The quick brown fox', 9));
        self::assertSame('abc', Str::limit('abc', 9));
        self::assertSame('abc', Str::limit('abc', 3));
        self::assertSame('ab>', Str::limit('abc', 2, '>'));
    }

    #[Test]
    public function limitRejectsNonPositiveLength(): void
    {
        $this->expectException(InvalidParamException::class);
        Str::limit('abc', 0);
    }

    #[Test]
    public function containsStartsWithEndsWith(): void
    {
        self::assertTrue(Str::contains('Hello World', 'World'));
        self::assertTrue(Str::contains('Hello', ['x', 'ell']));
        self::assertFalse(Str::contains('Hello', ['x', 'y']));
        self::assertFalse(Str::contains('Hello', ''));
        self::assertTrue(Str::contains('Hello', 'HELLO', true));
        self::assertFalse(Str::contains('Hello', 'HELLO'));

        self::assertTrue(Str::startsWith('abc', 'a'));
        self::assertTrue(Str::startsWith('abc', ['x', 'ab']));
        self::assertFalse(Str::startsWith('abc', ''));

        self::assertTrue(Str::endsWith('abc', 'c'));
        self::assertTrue(Str::endsWith('abc', ['x', 'bc']));
        self::assertFalse(Str::endsWith('abc', ''));
    }

    #[Test]
    public function squishTrimsAndCollapses(): void
    {
        self::assertSame('a b c', Str::squish("  a   b \n c  "));
        self::assertSame('', Str::squish('   '));
        self::assertSame('abc', Str::squish('abc'));
    }

    #[Test]
    public function afterBeforeBetween(): void
    {
        self::assertSame('b', Str::after('a=b', '='));
        self::assertSame('abc', Str::after('abc', 'x'));
        self::assertSame('abc', Str::after('abc', ''));

        self::assertSame('a', Str::before('a=b', '='));
        self::assertSame('abc', Str::before('abc', 'x'));
        self::assertSame('abc', Str::before('abc', ''));

        self::assertSame('tag', Str::between('[tag]', '[', ']'));
        self::assertSame('', Str::between('abc', '[', ']'));
        self::assertSame('', Str::between('[tag]', '', ']'));
    }

    #[Test]
    public function ucFirstAndLcFirstAreMultibyte(): void
    {
        self::assertSame('Привет', Str::ucFirst('привет'));
        self::assertSame('привет', Str::lcFirst('Привет'));
        self::assertSame('', Str::ucFirst(''));
        self::assertSame('', Str::lcFirst(''));
    }

    #[Test]
    public function conversionCachesAreBounded(): void
    {
        Str::clearCache();

        $cache = new \ReflectionProperty(Str::class, 'delimitedCache');

        for ($i = 0; $i < Str::CACHE_LIMIT * 2; $i++) {
            Str::toSnake("CachedValue$i");
        }

        self::assertLessThanOrEqual(Str::CACHE_LIMIT, count($cache->getValue()));
        // eviction must not corrupt the result
        self::assertSame('some_value', Str::toSnake('SomeValue'));

        Str::clearCache();
        self::assertCount(0, $cache->getValue());
    }

    #[Test]
    public function slugifyTrimsAndCollapsesSeparators(): void
    {
        // used to keep a dangling separator wherever the string ended in punctuation
        self::assertSame('hello-world', Str::slugify('Hello World!'));
        self::assertSame('hello', Str::slugify('  --Hello--  '));
        self::assertSame('privet-mir', Str::slugify('Привет мир'));
        self::assertSame('a-b', Str::slugify('a!!!b'));
        self::assertSame('already-ok', Str::slugify('already-ok'));
        self::assertSame('', Str::slugify('!!!'));
        self::assertSame('', Str::slugify(''));

        self::assertSame('hello_world', Str::slugify('Hello World!', '_'));
        self::assertSame('helloworld', Str::slugify('Hello World!', ''));
    }

    #[Test]
    public function caseConversionsFindWordBoundariesInAnyScript(): void
    {
        // the boundary detection used to compare against the ASCII ranges, so a non-Latin
        // string was merely lower-cased and never split
        self::assertSame('привет_мир', Str::toSnake('ПриветМир'));
        self::assertSame('привет-мир', Str::toKebab('ПриветМир'));
        self::assertSame('ελλάδα_χώρα', Str::toSnake('ΕλλάδαΧώρα'));
        self::assertSame('über_straße', Str::toSnake('ÜberStraße'));
        self::assertSame('ПРИВЕТ_МИР', Str::toScreamingSnake('ПриветМир'));
    }

    #[Test]
    public function toCamelKeepsNonAsciiLetters(): void
    {
        // toCamel used to drop every character outside the ASCII ranges outright:
        // 'ÜberStraße' came back as 'berStrae' and Cyrillic as an empty string
        self::assertSame('ÜberStraße', Str::toCamel('über_straße'));
        self::assertSame('ПриветМир', Str::toCamel('привет_мир'));
        self::assertSame('ПриветМир', Str::toCamel('Привет Мир'));
        self::assertSame('ΕλλάδαΧώρα', Str::toCamel('ελλάδα-χώρα'));
        self::assertSame('приветМир', Str::toLowerCamel('привет_мир'));
    }

    #[Test]
    public function asciiCaseConversionsAreUnchanged(): void
    {
        self::assertSame('json_data', Str::toSnake('JSONData'));
        self::assertSame('some_value', Str::toSnake('someValue'));
        self::assertSame('user_123_name', Str::toSnake('user123Name'));
        self::assertSame('SomeValue', Str::toCamel('some_value'));
        self::assertSame('someValue', Str::toLowerCamel('some_value'));
    }

    #[Test]
    public function padding(): void
    {
        self::assertSame('---abc---', Str::padBoth('abc', 9, '-'));
        self::assertSame('abc', Str::padBoth('abc', 3));
        self::assertSame('abc', Str::padBoth('abc', 1));
        // an odd remainder goes to the right
        self::assertSame('-abc--', Str::padBoth('abc', 6, '-'));

        self::assertSame('007', Str::padLeft('7', 3, '0'));
        self::assertSame('700', Str::padRight('7', 3, '0'));
        self::assertSame('7', Str::padLeft('7', 1, '0'));

        // multibyte: the length is in characters, not bytes
        self::assertSame('——ы——', Str::padBoth('ы', 5, '—'));
        self::assertSame(5, mb_strlen(Str::padBoth('ы', 5, '—')));
        // a multi-character pad is cut to fit exactly
        self::assertSame('abab7', Str::padLeft('7', 5, 'ab'));
    }

    #[Test]
    public function paddingRejectsAnEmptyPad(): void
    {
        $this->expectException(InvalidParamException::class);
        Str::padBoth('abc', 9, '');
    }

    #[Test]
    public function wrapAndTitle(): void
    {
        self::assertSame('"x"', Str::wrap('x', '"'));
        self::assertSame('<x>', Str::wrap('x', '<', '>'));

        self::assertSame('Hello World', Str::title('hello world'));
        self::assertSame('Привет Мир', Str::title('привет мир'));
        self::assertSame('', Str::title(''));
    }

    #[Test]
    public function uuidIsAValidVersion4(): void
    {
        $uuid = Str::uuid();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $uuid
        );
        self::assertNotSame($uuid, Str::uuid());
    }

    #[Test]
    public function ulidIsSortableByTime(): void
    {
        $earlier = Str::ulid(1_000);
        $later   = Str::ulid(2_000);

        self::assertSame(26, strlen($earlier));
        self::assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $earlier);
        self::assertLessThan($later, $earlier, 'ULIDs must sort by creation time');
        self::assertNotSame(Str::ulid(1_000), Str::ulid(1_000), 'the random part must differ');
    }
}
