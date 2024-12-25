<?php

declare(strict_types=1);

namespace Php\Support\Tests;

use Php\Support\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StorageTest extends TestCase
{
    #[Test]
    public function init(): void
    {
        $storage = new Storage();

        self::assertEquals([], $storage->jsonSerialize());
    }

    #[Test]
    public function setSimpleProp(): void
    {
        $storage = new Storage();
        $storage->test = 'name';

        self::assertEquals(['test' => 'name'], $storage->jsonSerialize());
    }

    #[Test]
    public function getSimpleProp(): void
    {
        $storage = new Storage();
        $storage->test = 'name';

        self::assertEquals('name', $storage->test);
    }

    #[Test]
    public function setPathProp(): void
    {
        $storage = new Storage();
        $storage->{'first.second'} = 'name';

        self::assertEquals(['first' => ['second' => 'name']], $storage->jsonSerialize());
    }

    #[Test]
    public function getPathProp(): void
    {
        $storage = new Storage();
        $storage->{'first.second'} = 'name';
        $storage->{'first.second2'} = 'test';

        self::assertEquals(['second' => 'name', 'second2' => 'test'], $storage->{'first'});
        self::assertEquals('name', $storage->{'first.second'});
        self::assertEquals('test', $storage->{'first.second2'});
    }

    #[Test]
    public function setFn(): void
    {
        $storage = new Storage();
        $storage->set('first.second', 'name');

        self::assertEquals(['first' => ['second' => 'name']], $storage->jsonSerialize());
    }

    #[Test]
    public function getFn(): void
    {
        $storage = new Storage();
        $storage->set('first.second', 'name');
        $storage->set('first.second2', 1);

        self::assertEquals(['second' => 'name', 'second2' => 1], $storage->get('first'));
        self::assertEquals('name', $storage->get('first.second'));
        self::assertEquals(1, $storage->get('first.second2'));
    }

    #[Test]
    public function remove(): void
    {
        $storage = new Storage();
        $storage->set('first.second', 'name');
        $storage->set('first.second2', 1);

        $storage->remove('first');
        self::assertEquals([], $storage->jsonSerialize());

        $storage->set('first.second', 'name');
        $storage->set('first.second2', 1);

        $storage->remove('first.second');

        self::assertEquals(['second2' => 1], $storage->get('first'));
        self::assertEquals(1, $storage->get('first.second2'));
        self::assertNull($storage->get('first.second'));
    }

    #[Test]
    public function unsetFn(): void
    {
        $storage = new Storage();
        $storage->set('first.second', 'name');
        $storage->set('first.second2', 1);

        unset($storage->{'first.second'});

        self::assertEquals(['second2' => 1], $storage->get('first'));
        self::assertEquals(1, $storage->get('first.second2'));
        self::assertNull($storage->get('first.second'));
    }

    #[Test]
    public function exist(): void
    {
        $storage = new Storage();
        self::assertFalse($storage->exist('first'));
        $storage->set('first', 1);

        self::assertTrue($storage->exist('first'));
    }

    #[Test]
    public function issetFn(): void
    {
        $storage = new Storage();
        self::assertFalse(isset($storage->first));
        $storage->set('first', 1);

        self::assertTrue(isset($storage->first));
    }

    #[Test]
    public function offsets(): void
    {
        $storage = new Storage();
        self::assertFalse(isset($storage['first']));
        $storage['first'] = 1;

        self::assertTrue(isset($storage['first']));
        self::assertEquals(1, $storage['first']);

        unset($storage['first']);
        self::assertNull($storage['first']);
        self::assertEquals([], $storage->jsonSerialize());

    }
}