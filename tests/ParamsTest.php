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

}
