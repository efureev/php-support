<?php
declare(strict_types=1);

use Php\Support\Helpers\Json;
use PHPUnit\Framework\TestCase;

final class JsonTest extends TestCase
{

    public function testEncode()
    {
        // Arrayable data encoding
        $dataArrayable = $this->getMockBuilder('Php\\Support\\Interfaces\\Arrayable')->getMock();
        $dataArrayable->method('toArray')->willReturn([]);

        $actual = Json::encode($dataArrayable);
        $this->assertSame('[]', $actual);
        // basic data encoding
        $data = '1';
        $this->assertSame('"1"', Json::encode($data));
        // simple array encoding
        $data = [1, 2];
        $this->assertSame('[1,2]', Json::encode($data));
        $data = ['a' => 1, 'b' => 2];
        $this->assertSame('{"a":1,"b":2}', Json::encode($data));
        // simple object encoding
        $data = new \stdClass();
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

        $data = new JsonModel();
        $data->data = [];
        $this->assertSame('[]', Json::encode($data));
        $data = new JsonModel();
        $data->data = (object)null;
        $this->assertSame('[]', Json::encode($data));
    }

    public function testHtmlEncode()
    {
        // HTML escaped chars
        $data = '&<>"\'/';
        $this->assertSame('"\u0026\u003C\u003E\u0022\u0027\/"', Json::htmlEncode($data));
        // basic data encoding
        $data = '1';
        $this->assertSame('"1"', Json::htmlEncode($data));
        // simple array encoding
        $data = [1, 2];
        $this->assertSame('[1,2]', Json::htmlEncode($data));
        $data = ['a' => 1, 'b' => 2];
        $this->assertSame('{"a":1,"b":2}', Json::htmlEncode($data));
        // simple object encoding
        $data = new \stdClass();
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

    public function testDecode()
    {
        // empty value
        $json = '';
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
        $this->expectException('Php\Support\Exceptions\InvalidArgumentException');
        Json::decode($json);
    }

    /**
     * @expectedException \Php\Support\Exceptions\InvalidArgumentException
     * @expectedExceptionMessage Syntax error.
     */
    public function testDecodeInvalidParamException()
    {
        Json::decode('sa');
    }

    public function testHandleJsonError()
    {
        // Basic syntax error
        try {
            $json = "{'a': '1'}";
            Json::decode($json);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Php\Support\Exceptions\InvalidArgumentException::class, $e);
            $this->assertSame(Json::$jsonErrorMessages['JSON_ERROR_SYNTAX'], $e->getMessage());
        }
        // Unsupported type since PHP 5.5
        try {
            $fp = fopen('php://stdin', 'r');
            $data = ['a' => $fp];
            Json::encode($data);
            fclose($fp);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Php\Support\Exceptions\InvalidArgumentException::class, $e);
            if (PHP_VERSION_ID >= 50500) {
                $this->assertSame(Json::$jsonErrorMessages['JSON_ERROR_UNSUPPORTED_TYPE'], $e->getMessage());
            } else {
                $this->assertSame(Json::$jsonErrorMessages['JSON_ERROR_SYNTAX'], $e->getMessage());
            }
        }
    }


}

class JsonModel implements \JsonSerializable
{
    public $data = ['json' => 'serializable'];

    public function jsonSerialize()
    {
        return $this->data;
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 100]
        ];
    }

    public function init()
    {

    }
}
