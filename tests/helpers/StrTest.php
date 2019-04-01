<?php
declare(strict_types=1);

use Php\Support\Helpers\Str;
use PHPUnit\Framework\TestCase;

/**
 * Class StrTest
 */
final class StrTest extends TestCase
{

    public function providerDataSnake(): array
    {
        return [
            ['', ''],
            ['    ', ''],
            ['test', 'test'],
            ['testCase', 'test_case'],
            ['TestCase', 'test_case'],
            ['Test Case', 'test_case'],
            ['Ta C', 'ta_c'],
            ['T Ca', 't_ca'],
            ['Test   Case', 'test_case'],
            ['test_case', 'test_case'],
            ['Test   test_case', 'test_test_case'],
            ['test test', 'test_test'],
            ['test TEST 111', 'test_test_111'],
            ['TEST TEST TEST', 'test_test_test'],
            ['123 313 212', '123_313_212'],
            ['testTestGrab12', 'test_test_grab_12'],
            ['  test Test grab12  ', 'test_test_grab_12'],
            ['AAbbb', 'a_abbb'],
            ['userID', 'user_id'],
            ['JSONData', 'json_data'],
            ['AnyKind of_string', 'any_kind_of_string'],
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
            ['', ''],
            ['    ', ''],
            ['test', 'TEST'],
            ['testCase', 'TEST_CASE'],
            ['TestCase', 'TEST_CASE'],
            ['Test Case', 'TEST_CASE'],
            ['Ta C', 'TA_C'],
            ['T Ca', 'T_CA'],
            ['Test   Case', 'TEST_CASE'],
            ['test_case', 'TEST_CASE'],
            ['Test   test_case', 'TEST_TEST_CASE'],
            ['test test', 'TEST_TEST'],
            ['test TEST 111', 'TEST_TEST_111'],
            ['TEST TEST TEST', 'TEST_TEST_TEST'],
            ['123 313 212', '123_313_212'],
            ['testTestGrab12', 'TEST_TEST_GRAB_12'],
            ['  test Test grab12  ', 'TEST_TEST_GRAB_12'],
            ['AAbbb', 'A_ABBB'],
            ['userID', 'USER_ID'],
            ['JSONData', 'JSON_DATA'],
            ['AnyKind of_string', 'ANY_KIND_OF_STRING'],
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
            ['', ''],
            ['    ', ''],
            ['test', 'test'],
            ['testCase', 'test-case'],
            ['TestCase', 'test-case'],
            ['Test Case', 'test-case'],
            ['Ta C', 'ta-c'],
            ['T Ca', 't-ca'],
            ['Test   Case', 'test-case'],
            ['test_case', 'test-case'],
            ['Test   test_case', 'test-test-case'],
            ['test test', 'test-test'],
            ['test TEST 111', 'test-test-111'],
            ['TEST TEST TEST', 'test-test-test'],
            ['123 313 212', '123-313-212'],
            ['testTestGrab12', 'test-test-grab-12'],
            ['  test Test grab12  ', 'test-test-grab-12'],
            ['AAbbb', 'a-abbb'],
            ['userID', 'user-id'],
            ['JSONData', 'json-data'],
            ['AnyKind of_string', 'any-kind-of-string'],
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
            ['', ''],
            ['    ', ''],
            ['test_case', 'TestCase'],
            ['testCase', 'TestCase'],
            ['TestCase', 'TestCase'],
            [' test  case ', 'TestCase'],
            [' many_many_words', 'ManyManyWords'],
            ['AnyKind of_string', 'AnyKindOfString'],
            ['odd-fix', 'OddFix'],
            ['numbers2And55with000', 'Numbers2And55With000'],
            ['numbe-55w_it 000', 'Numbe55wIt000'],
            ['-55w_it 000', '55wIt000'],
            ['55w_it 000', '55wIt000'],
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
            ['', ''],
            ['    ', ''],
            ['test_case', 'testCase'],
            ['testCase', 'testCase'],
            ['TestCase', 'testCase'],
            [' test  case ', 'testCase'],
            [' many_many_words', 'manyManyWords'],
            ['AnyKind of_string', 'anyKindOfString'],
            ['odd-fix', 'oddFix'],
            ['numbers2And55with000', 'numbers2And55With000'],
            ['numbe-55w_it 000', 'numbe55wIt000'],
            ['-55w_it 000', '55wIt000'],
            ['55w_it 000', '55wIt000'],
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
//        var_dump(Str::$delimitedCache,count(Str::$delimitedCache));

        $result = Str::toSnake('ets_Case  12');
        static::assertEquals('ets_case_12', $result);
//        var_dump(Str::$delimitedCache,count(Str::$delimitedCache));

        $result = Str::toSnake('  ets_Case 12 ');
        static::assertEquals('ets_case_12', $result);
//        var_dump(Str::$delimitedCache, count(Str::$delimitedCache));
    }

}
