<?php

declare(strict_types=1);

use Php\Support\Exceptions\JsonException;
use Php\Support\Helpers\Json;
use PHPUnit\Framework\TestCase;

/**
 * Class JsonTest
 */
final class JsonTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws JsonException
     */
    public function testEncode(): void
    {
        // Arrayable data encoding
        $data_arrayable = $this->getMockBuilder(\Php\Support\Interfaces\Arrayable::class)->getMock();
        $data_arrayable->method('toArray')->willReturn([]);

        $actual = Json::encode($data_arrayable);
        $this->assertSame('[]', $actual);
        // basic data encoding
        $data = '1';
        $this->assertSame('"1"', Json::encode($data));
        // simple array encoding
        $data = [
            1,
            2,
        ];
        $this->assertSame('[1,2]', Json::encode($data));
        $data = [
            'a' => 1,
            'b' => 2,
        ];
        $this->assertSame('{"a":1,"b":2}', Json::encode($data));
        // simple object encoding
        $data    = new \stdClass();
        $data->a = 1;
        $data->b = 2;
        $this->assertSame('[]', Json::encode($data));
        // empty data encoding
        $data = [];
        $this->assertSame('[]', Json::encode($data));
        $data = new \stdClass();
        $this->assertSame('[]', Json::encode($data));

        $data = (object)null;
        $this->assertSame('[]', Json::encode($data));
        // JsonSerializable
        $data = new JsonModel();
        $this->assertSame('{"json":"serializable"}', Json::encode($data));

        $data       = new JsonModel();
        $data->data = [];
        $this->assertSame('[]', Json::encode($data));
        $data       = new JsonModel();
        $data->data = (object)null;
        $this->assertSame('[]', Json::encode($data));
    }

    /**
     * @throws JsonException
     */
    public function testHtmlEncode(): void
    {
        // HTML escaped chars
        $data = '&<>"\'/';
        $this->assertSame('"\u0026\u003C\u003E\u0022\u0027\/"', Json::htmlEncode($data));
        // basic data encoding
        $data = '1';
        $this->assertSame('"1"', Json::htmlEncode($data));
        // simple array encoding
        $data = [
            1,
            2,
        ];
        $this->assertSame('[1,2]', Json::htmlEncode($data));
        $data = [
            'a' => 1,
            'b' => 2,
        ];
        $this->assertSame('{"a":1,"b":2}', Json::htmlEncode($data));
        // simple object encoding
        $data    = new \stdClass();
        $data->a = 1;
        $data->b = 2;
        $this->assertSame('[]', Json::htmlEncode($data));

        $data = (object)null;
        $this->assertSame('[]', Json::htmlEncode($data));
        // JsonSerializable
        $data = new JsonModel();
        $this->assertSame('{"json":"serializable"}', Json::htmlEncode($data));

//        $postsStack = new \SplStack();
//        $postsStack->push(new Post(915, 'record1'));
//        $postsStack->push(new Post(456, 'record2'));
//        $this->assertSame('{"1":{"id":456,"title":"record2"},"0":{"id":915,"title":"record1"}}', Json::encode($postsStack));
    }

    /**
     * @throws JsonException
     */
    public function testDecode(): void
    {
        // empty value
        $json   = '';
        $actual = Json::decode($json);
        $this->assertNull($actual);
        // basic data decoding
        $json = '"1"';
        $this->assertSame('1', Json::decode($json));
        // array decoding
        $json = '{"a":1,"b":2}';
        $this->assertSame(['a' => 1, 'b' => 2], Json::decode($json));
        // exception
        $json = '{"a":1,"b":2';
        $this->expectException(JsonException::class);
        Json::decode($json);
    }

    /**
     * @throws JsonException
     */
    public function testDecodeInvalidParamException(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Syntax error.');

        Json::decode('sa');
    }


    /**
     *
     */
    public function testHandleJsonError(): void
    {
        try {
            $json = "{'a': '1'}";
            Json::decode($json);
        } catch (Throwable $exception) {
            $this->assertInstanceOf(JsonException::class, $exception);
            $this->assertSame(JsonException::ERRORS_MESSAGES[JSON_ERROR_SYNTAX], $exception->getMessage());
        }

        // Unsupported type since PHP 5.5
        try {
            $fp   = fopen('php://stdin', 'rb');
            $data = ['a' => $fp];
            Json::encode($data);
            fclose($fp);
        } catch (Throwable $exception) {
            $this->assertInstanceOf(JsonException::class, $exception);
            if (PHP_VERSION_ID >= 50500) {
                $this->assertSame(JsonException::ERRORS_MESSAGES[JSON_ERROR_UNSUPPORTED_TYPE], $exception->getMessage());
            } else {
                $this->assertSame(JsonException::ERRORS_MESSAGES[JSON_ERROR_SYNTAX], $exception->getMessage());
            }
        }
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
     * @dataProvider providerToArray
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

    public function jsonSerialize()
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
                'name', 'string', 'max' => 100
            ],
        ];
    }

    public function init(): void
    {
    }
}
