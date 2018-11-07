<?php
declare(strict_types=1);

use Php\Support\Components\BaseObject;
use PHPUnit\Framework\TestCase;

final class BaseObjectTest extends TestCase
{

    public function testCanBeInstance(): void
    {
        $this->assertInstanceOf(BaseObject::class, new BaseObject());
    }

    public function testCanBeEqualClassName(): void
    {
        $this->assertEquals(BaseObject::className(), BaseObject::class);
        $this->assertEquals(BaseObject::shortClassName(), 'BaseObject');
    }

    public function testMagicMethods(): void
    {
        $cls = new class() extends BaseObject
        {
            public $prop;

            public function getKeyName()
            {
                return $this->prop;
            }

            public function setKeyName($val)
            {
                $this->prop = $val;
            }
        };


        $this->assertNull($cls->prop);
        $cls->keyName = 'val 1';

        $this->assertEquals('val 1', $cls->prop);
        $this->assertEquals('val 1', $cls->getKeyName());
        $this->assertEquals('val 1', $cls->keyName);
        $this->assertNotEquals('val 2', $cls->keyName);

        unset($cls->keyName);
        $this->assertNull($cls->prop);
    }

}
