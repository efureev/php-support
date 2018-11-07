<?php
declare(strict_types=1);

use Php\Support\Components\Params;
use PHPUnit\Framework\TestCase;

final class ParamsTest extends TestCase
{
    public static function values()
    {
        return [
            'key'        => 'value',
            'int1'       => 2,
            'int2'       => -12,
            'array'      => [1, 2, 3, 4, 5],
            'string'     => 'string value',
            'null'       => null,
            'false'      => false,
            'true'       => true,
            'float'      => 12.31,
            'empty'      => '',
            'emptyArray' => [],
            'cls'        => new stdClass(),
            'clsAnon'    => new class()
            {
                private $private = 'priv';
                protected $protected = 'prot';
                public $public = 'pub';

                function __toString()
                {
                    return "{$this->public}:{$this->protected}:{$this->private}";
                }
            }
        ];
    }

    public function testCanBeInstanceEmpty(): void
    {
        $params = new Params();
        $this->assertInstanceOf(Params::class, $params);
    }

    public function testCanBeInstanceFillDifferentValues(): void
    {
        $params = new Params(static::values());
        $this->assertInstanceOf(
            Params::class,
            $params
        );
    }

    public function testCanBeGetByKey(): void
    {
        $params = new Params(static::values());
        $keys = ['int2', 'false', 'string', 'emptyArray'];
        $array = $params->toArray($keys);

        $this->assertCount(count($keys), $array);
        $this->assertEquals($params->offsetGet('int2'), $array['int2']);
        $this->assertEquals($params->string, $array['string']);
        $this->assertEquals($params->emptyArray, $array['emptyArray']);
    }

    public function testCanBeCollect(): void
    {

        $phone1 = [
            "id"           => 1,
            "type"         => "PHONE",
            "notification" => "false",
            "phone"        => "8-4912-25-97-22"
        ];
        $phone2 = [
            "id"           => 2,
            "type"         => "FAX",
            "notification" => "false",
            "phone"        => "8-4912-25-97-22"
        ];
        $cls = new class extends Params
        {
        };
        $phones = new Params([$phone1, $phone2]);

        $this->assertCount(2, $phones);
        $this->assertEquals($phones->toArray(), [$phone1, $phone2]);
        $this->assertEquals($phones->toJson(), '[{"id":1,"type":"PHONE","notification":"false","phone":"8-4912-25-97-22"},{"id":2,"type":"FAX","notification":"false","phone":"8-4912-25-97-22"}]');

    }

}
