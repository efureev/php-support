<?php
declare(strict_types=1);

use Php\Support\Components\ParamsJson;
use PHPUnit\Framework\TestCase;

final class ParamsJsonTest extends TestCase
{
    protected static $contact_1 = [
        "id"           => 1,
        "type"         => "PHONE",
        "notification" => "false",
        "contact"      => "8-4912-25-97-22"
    ];

    protected static $contact_2 = [
        "type"         => "email",
        "notification" => "true",
        "contact"      => "mail@yahoo.com"
    ];

    protected static $resultOneJson = '{"id":1,"type":"PHONE","notification":"false","contact":"8-4912-25-97-22"}';
    protected static $resultJson = '{"_type":"Contact","_data":[{"id":1,"type":"PHONE","notification":"false","contact":"8-4912-25-97-22"},{"type":"email","notification":"true","contact":"mail@yahoo.com"}]}';


    private static function valuesCollection()
    {
        return new Contacts([static::$contact_1, static::$contact_2]);
    }

    private static function values()
    {
        return new Contact(static::$contact_1);
    }

    public function testCanBeOneInstance(): void
    {
        $contact = self::values();

        $this->assertInstanceOf(Contact::class, $contact);
    }

    public function testCanBeEmpty(): void
    {
        $contacts = new Contacts();

        $this->assertInstanceOf(Contacts::class, $contacts);
        $this->assertCount(0, $contacts);

        $this->assertEquals('[]', (string)$contacts);
        $this->assertEquals('[]', $contacts->toJson());
    }

    public function testOneInstanceCanBeJson(): void
    {
        $contact = self::values();

        $this->assertEquals(self::$resultOneJson, $contact->toJson());
        $this->assertEquals("PHONE", $contact->type);
        $this->assertJson($contact->toJson());
    }

    public function testOneInstanceCanBeLoadFrom(): void
    {
        $contact = Contact::fromJson(self::$resultOneJson);
        $this->assertInstanceOf(Contact::class, Contact::fromJson(self::$resultOneJson));

        $this->assertNull($contact->getElementsType());
        $this->assertEquals(self::$resultOneJson, $contact->toJson());

        $contact = new Contact;
        $contact->setElementsType('string')->fromJsonString(self::$resultOneJson);

        foreach ($contact->toArray() as $field => $element) {
            $this->assertInternalType('string', $element);
        }

    }

    public function testOneInstanceCanBeChangeType(): void
    {
        $contact = Contact::fromJson(self::$resultOneJson);
        $this->assertInstanceOf(Contact::class, Contact::fromJson(self::$resultOneJson));

        $this->assertInternalType('integer', $contact->id);

        $contact->setElementsType('string');
        foreach ($contact->toArray() as $field => $element) {
            $this->assertInternalType('string', $element);
        }

        $contact->setElementsType('array');
        foreach ($contact->toArray() as $field => $element) {
            $this->assertInternalType('array', $element);
        }
    }

    public function testCollectionCanBeInstances(): void
    {
        $contacts = self::valuesCollection();

        $this->assertInstanceOf(Contacts::class, $contacts);

        /**
         * @var int     $key
         * @var Contact $contact
         */
        foreach ($contacts as $key => $contact) {
            $this->assertInstanceOf($contacts->getElementsType(), $contact);
        }
    }

    public function testCollectionCanBeJsonString(): void
    {
        $contacts = self::valuesCollection();

        $this->assertEquals(self::$resultJson, $contacts->toJson());
    }

    public function testCollectionCanBeLoadFromJson(): void
    {
        $contacts = Contacts::fromJson(self::$resultJson);

        $this->assertEquals(self::$resultJson, $contacts->toJson());

        /**
         * @var int     $key
         * @var Contact $contact
         */
        foreach ($contacts as $key => $contact) {
            $this->assertInstanceOf($contacts->getElementsType(), $contact);

            $this->assertArrayHasKey('type', $contact->toArray());
            $this->assertJson($contact->toJson());
        }

    }
}

class Contact extends ParamsJson
{
}

class Contacts extends ParamsJson
{
    protected $_type = Contact::class;
}
