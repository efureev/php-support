<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GetterTest extends TestCase
{

    public function testInstance(): void
    {
        $cls = new class()
        {
            use \Php\Support\Traits\Getter;
            protected $prop = 'secret';

            public function getKeyName()
            {
                return $this->prop;
            }

            public function setWriteOnly($val)
            {
                $this->prop = $val;
            }
        };

        $this->assertEquals('secret', $cls->keyName);
        $this->assertTrue(isset($cls->keyName));
        $this->assertFalse(isset($cls->val));
        $this->assertEquals('getJackDaniels', $cls::getter('jackDaniels'));

//        $cls->writeOnly = 'success';

//        var_dump($cls);die;
//        $this->assertEquals('success', $cls->getKeyName());

        try {
            $this->assertEquals('success', $cls->writeOnly);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(Php\Support\Exceptions\InvalidCallException::class, $e);
        }


    }

}
