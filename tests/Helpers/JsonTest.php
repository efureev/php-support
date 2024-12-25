<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

use JsonException;
use Php\Support\Helpers\Json;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Class JsonTest
 */
final class JsonTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testEncode(): void
    {
        // Arrayable data encoding
        $data_arrayable = $this->getMockBuilder(\Php\Support\Interfaces\Arrayable::class)->getMock();
        $data_arrayable->method('toArray')->willReturn([]);

        $actual = Json::encode($data_arrayable);
        self::assertSame('[]', $actual);
        // basic data encoding
        $data = '1';
        self::assertSame('"1"', Json::encode($data));
        // simple array encoding
        $data = [
            1,
            2,
        ];
        self::assertSame('[1,2]', Json::encode($data));
        $data = [
            'a' => 1,
            'b' => 2,
        ];
        self::assertSame('{"a":1,"b":2}', Json::encode($data));
        // simple object encoding
        $data    = new \stdClass();
        $data->a = 1;
        $data->b = 2;
        self::assertSame('[]', Json::encode($data));
        // empty data encoding
        $data = [];
        self::assertSame('[]', Json::encode($data));
        $data = new \stdClass();
        self::assertSame('[]', Json::encode($data));

        $data = (object)null;
        self::assertSame('[]', Json::encode($data));
        // JsonSerializable
        $data = new JsonModel();
        self::assertSame('{"json":"serializable"}', Json::encode($data));

        $data       = new JsonModel();
        $data->data = [];
        self::assertSame('[]', Json::encode($data));
        $data       = new JsonModel();
        $data->data = (object)null;
        self::assertSame('[]', Json::encode($data));
    }

    /**
     */
    public function testHtmlEncode(): void
    {
        // HTML escaped chars
        $data = '&<>"\'/';
        self::assertSame('"\u0026\u003C\u003E\u0022\u0027\/"', Json::htmlEncode($data));
        // basic data encoding
        $data = '1';
        self::assertSame('"1"', Json::htmlEncode($data));
        // simple array encoding
        $data = [
            1,
            2,
        ];
        self::assertSame('[1,2]', Json::htmlEncode($data));
        $data = [
            'a' => 1,
            'b' => 2,
        ];
        self::assertSame('{"a":1,"b":2}', Json::htmlEncode($data));
        // simple object encoding
        $data    = new \stdClass();
        $data->a = 1;
        $data->b = 2;
        self::assertSame('[]', Json::htmlEncode($data));

        $data = (object)null;
        self::assertSame('[]', Json::htmlEncode($data));
        // JsonSerializable
        $data = new JsonModel();
        self::assertSame('{"json":"serializable"}', Json::htmlEncode($data));
    }

    /**
     */
    public function testDecode(): void
    {
        // empty value
        $json   = '';
        $actual = Json::decode($json);
        self::assertNull($actual);
        // basic data decoding
        $json = '"1"';
        self::assertSame('1', Json::decode($json));
        self::assertSame('1', Json::decode($json, true, JSON_INVALID_UTF8_IGNORE));
        // array decoding
        $json = '{"a":1,"b":2}';
        self::assertSame(['a' => 1, 'b' => 2], Json::decode($json));

        self::assertEquals([], Json::decode('{}', true, JSON_THROW_ON_ERROR, 2));
        self::assertEquals([], Json::decode("{}", true, JSON_THROW_ON_ERROR, 2));
        self::assertEquals([], Json::decode("[]", true, JSON_THROW_ON_ERROR, 2));
        // exception
        $json = '{"a":1,"b":2';
//        $this->expectException(JsonException::class);
        self::assertNull(Json::decode($json, true, JSON_THROW_ON_ERROR));
    }

    /**
     */
    public function testDecodeInvalidParamException(): void
    {
//        $this->expectException(JsonException::class);
//        $this->expectExceptionMessage('Syntax error');

        $res = Json::decode('sa', true, JSON_THROW_ON_ERROR);
        self::assertNull($res);
    }

    public function testDecodeInvalidParamException2(): void
    {
        $res = Json::decode('sa');
        self::assertNull($res);
        self::assertEquals(JSON_ERROR_SYNTAX, json_last_error());
    }


    /**
     *
     */
    public function testHandleJsonError(): void
    {
        $json = "{'a': '1'}";
        static::assertNull(Json::decode($json, ));
        static::assertNull(Json::decode($json, true, JSON_THROW_ON_ERROR));
    }


    /**
     * @return array
     */
    /*public function providerToArray(): array
    {
        return [
            [[1, 2, 3], [1, 2, 3]],
            [[], []],
            [[null], [null]],
            [1, [1]],
            ['test', ['test']],
            [new class () implements \Php\Support\Interfaces\Arrayable
            {
                private $data = ['1', 2, 'test'];

                public function toArray(): array
                {
                    return $this->data;
                }

            }, ['1', 2, 'test']],
            [new class () implements \Php\Support\Interfaces\Jsonable
            {
                private $data = ['32', 12, 'test'];

                public static function fromJson(string $json): ?Jsonable
                {
                    return new self;
                }

                public function toJson($options = 320): ?string
                {
                    return Json::encode($this->data, $options);
                }

            }, ['32', 12, 'test']],
            [new class () implements \JsonSerializable
            {
                private $data = ['132', 12, 'test'];

                public function jsonSerialize()
                {
                    return $this->data;
                }

            }, ['132', 12, 'test']],
            [new ArrayObject([12, 'test 1']), [12, 'test 1']],
        ];
    }*/

    /**
     *  dataProvider providerToArray
     *
     * @param $items
     * @param $exp
     *
     * @throws JsonException
     */
    /*public function testDataToArray($items, $exp): void
    {
        $result = Arr::toArray($items);

        static::assertTrue(
            empty(array_diff_key($exp, $result)) && empty(array_diff_key($result, $exp))
        );
    }*/
}

/**
 * Class JsonModel
 */
class JsonModel implements \JsonSerializable
{
    /** @var array */
    public $data = ['json' => 'serializable'];

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [
                'name',
                'required',
            ],
            [
                'name',
                'string',
                'max' => 100,
            ],
        ];
    }

    public function init(): void
    {
    }
}
