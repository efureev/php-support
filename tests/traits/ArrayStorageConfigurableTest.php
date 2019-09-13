<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Sitesoft\Hub\Modules\Entity\ModuleConfig;

/**
 * Class ArrayStorageConfigurableTest
 */
final class ArrayStorageConfigurableTest extends TestCase
{
    public function testConfigurableSetAndGet(): void
    {
        $config = ArrayStorageConfigurableClassTest::make(['name' => 'Damn', 'id' => 'test', 'k' => 1, 's.k.d' => 2, 'remote' => true]);

        static::assertEquals('Damn', $config->name);
        static::assertEquals('test', $config->id);
        static::assertEquals(true, $config->remote);

        static::assertEquals(1, $config->get('k'));
        static::assertEquals(2, $config->get('s.k.d'));
        static::assertIsArray($config->getData());
        static::assertNotEmpty($config->getData());

        static::assertTrue(property_exists($config, 'name'));
//        $this->assertTrue(property_exists($config, 'id'));
        static::assertEquals('test', $config->get('id'));
        static::assertTrue(isset($config->id));
        static::assertTrue(isset($config->k));
    }

}


/**
 * Class ArrayStorageConfigurableClassTest
 */
class ArrayStorageConfigurableClassTest
{
    use \Php\Support\Traits\ArrayStorageConfigurableTrait;
    use \Php\Support\Traits\Maker;

    protected $name;

    public function __construct(array $data = [])
    {
        $this->configurable($data);
    }
}
