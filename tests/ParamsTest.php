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

        $phones = new Params([$phone1, $phone2]);

        $this->assertCount(2, $phones);
        $this->assertEquals($phones->toArray(), [$phone1, $phone2]);

        $json = '[{"id":1,"type":"PHONE","notification":"false","phone":"8-4912-25-97-22"},{"id":2,"type":"FAX","notification":"false","phone":"8-4912-25-97-22"}]';
        $this->assertEquals($phones->toJson(), $json);

        $params = Params::fromJson($json);

        $this->assertEquals($params->toJson(), $json);
        $this->assertEquals($params, $phones);
    }

    public function testCanBeJson(): void
    {
        $phone1 = new Params([
            "id"           => 1,
            "type"         => "PHONE",
            "notification" => "false",
            "phone"        => "8-4912-25-97-22"
        ]);

        $phones = new Params([$phone1]);

        $json = '[{"id":1,"type":"PHONE","notification":"false","phone":"8-4912-25-97-22"}]';
        $this->assertEquals($phones->toJson(), $json);

    }

    public function testFromJson(): void
    {
        $phone1 = new Phone([
            "id"           => 1,
            "type"         => "PHONE",
            "notification" => "false",
            "phone"        => "8-4912-25-97-22"
        ]);

        $phones = (new Phones)->fromArray([$phone1]);

        $json = '[{"id":1,"type":"PHONE","notification":"false","phone":"8-4912-25-97-22"}]';
        $this->assertEquals($phones->toJson(), $json);

        $newPhones = Phones::fromJson($json);

        $this->assertInstanceOf(Phones::class, $newPhones);

        /**
         * @var int   $key
         * @var Phone $element
         */
        foreach ($newPhones as $key => $element) {
            $this->assertInternalType('array', $element);

            $this->assertEquals($phones[ $key ]['id'], $element['id']);
        }
    }


    public function testInstanceCanAddObjectElement(): void
    {
        $phones = new Phones;

        $this->assertEquals(0, $phones->add(new Phone(['id' => 1])));
        $this->assertEquals(1, $phones->add(new Phone(['id' => 2])));
        $this->assertCount(2, $phones);

        $phone1 = $phones->get(0);
        $this->assertInstanceOf(Phone::class, $phone1);

        $phone2 = $phones->get(1);
        $this->assertInstanceOf(Phone::class, $phone2);

    }

    public function testInstanceCanAddObjectElementByIndex(): void
    {
        $phones = new Phones;
        $key1 = 'phone 1';
        $key2 = 'phone 2';

        $this->assertEquals($key1, $phones->add(new Phone(['id' => 1]), $key1));
        $this->assertEquals($key2, $phones->add(new Phone(['id' => 2]), $key2));
        $this->assertCount(2, $phones);

        $phone1 = $phones->get($key1);
        $this->assertInstanceOf(Phone::class, $phone1);

        $phone2 = $phones->get($key2);
        $this->assertInstanceOf(Phone::class, $phone2);

    }

    public function testInstanceCanAddObjectElementByPrimaryKey(): void
    {
        $phones = (new Phones)->setUniqueKeyName('id');
        $hash1 = $phones->add(new Phone(['id' => 1]));
        $hash2 = $phones->add(new Phone(['id' => 2]));

        $this->assertEquals(1, $hash1);
        $this->assertEquals(2, $hash2);

        $this->assertCount(2, $phones);

        $phone1 = $phones->get($hash1);
        $this->assertInstanceOf(Phone::class, $phone1);
        $this->assertEquals(1, $phone1->id);

        $phone2 = $phones->get($hash2);
        $this->assertInstanceOf(Phone::class, $phone2);
        $this->assertEquals(2, $phone2->id);
        $this->assertEquals('{"1":{"id":1},"2":{"id":2}}', $phones->toJson());
    }

    public function testInstanceCanAddObjectElementByDynamicHash(): void
    {
        $phones = (new Phones)->setDynamicHashKeys(['type', 'val']);
        $hash1 = $phones->add(new Phone(['type' => 'phone', 'val' => '8-4912-25-97-21']));
        $hash2 = $phones->add(new Phone(['type' => 'fax', 'val' => '8-4912-25-97-22']));

        $this->assertInternalType('string', $hash1);
        $this->assertInternalType('string', $hash2);
        $this->assertNotNull($hash1);
        $this->assertNotNull($hash2);

        $this->assertCount(2, $phones);

        $phone1 = $phones->get($hash1);
        $this->assertInstanceOf(Phone::class, $phone1);

        $phone2 = $phones->get($hash2);
        $this->assertInstanceOf(Phone::class, $phone2);
        $this->assertEquals('8-4912-25-97-21', $phone1->val);
        $this->assertEquals('8-4912-25-97-22', $phone2->val);
        $this->assertEquals('fax', $phone2->type);

    }

    public function testInstanceCanAddSimpleElements(): void
    {
        $phones = new Phones;
        $hash1 = $phones->add('phone 1');
        $hash2 = $phones->add('phone 2');
        $hash3 = $phones->add(3);

        $this->assertInternalType('integer', $hash1);
        $this->assertInternalType('integer', $hash2);
        $this->assertInternalType('integer', $hash3);
        $this->assertNotNull($hash1);
        $this->assertNotNull($hash2);
        $this->assertNotNull($hash3);

        $this->assertCount(3, $phones);

        $phone1 = $phones->get($hash1);
        $this->assertInternalType('string', $phone1);
        $phone2 = $phones->get($hash2);
        $this->assertInternalType('string', $phone2);
        $phone3 = $phones->get($hash3);
        $this->assertInternalType('integer', $phone3);

        $this->assertEquals('phone 2', $phone2);
        $this->assertEquals(3, $phone3);

    }

    public function testInstanceCanAddSimpleElementsDynamicKeys(): void
    {
        $phones = (new Phones)->setDynamicHashKeys(['type', 'val']);
        $hash1 = $phones->add('phone 1');
        $hash2 = $phones->add('phone 2');
        $hash3 = $phones->add(3);

        $this->assertInternalType('integer', $hash1);
        $this->assertInternalType('integer', $hash2);
        $this->assertInternalType('integer', $hash3);
        $this->assertNotNull($hash1);
        $this->assertNotNull($hash2);
        $this->assertNotNull($hash3);

        $this->assertCount(3, $phones);

        $phone1 = $phones->get($hash1);
        $this->assertInternalType('string', $phone1);
        $phone2 = $phones->get($hash2);
        $this->assertInternalType('string', $phone2);
        $phone3 = $phones->get($hash3);
        $this->assertInternalType('integer', $phone3);

        $this->assertEquals('phone 2', $phone2);
        $this->assertEquals(3, $phone3);

    }

    public function testInstanceCanAddSimpleElementsByUniqueKeys(): void
    {
        $phones = (new Phones)->setUniqueKeyName('id');
        $hash1 = $phones->add('phone 1');
        $hash2 = $phones->add('phone 2');
        $hash3 = $phones->add(3);

        $this->assertInternalType('integer', $hash1);
        $this->assertInternalType('integer', $hash2);
        $this->assertInternalType('integer', $hash3);
        $this->assertNotNull($hash1);
        $this->assertNotNull($hash2);
        $this->assertNotNull($hash3);

        $this->assertCount(3, $phones);

        $phone1 = $phones->get($hash1);
        $this->assertInternalType('string', $phone1);
        $phone2 = $phones->get($hash2);
        $this->assertInternalType('string', $phone2);
        $phone3 = $phones->get($hash3);
        $this->assertInternalType('integer', $phone3);

        $this->assertEquals('phone 2', $phone2);
        $this->assertEquals(3, $phone3);

    }

}

class Phone extends Params
{
}

class Phones extends Params
{

}
