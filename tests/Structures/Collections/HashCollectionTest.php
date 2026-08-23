<?php

declare(strict_types=1);

namespace Php\Support\Tests\Structures\Collections;

use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Structures\Collections\HashCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HashCollectionTest extends TestCase
{
    #[Test]
    public function constructAndAll(): void
    {
        self::assertSame([], (new HashCollection())->all());

        $c = new HashCollection(['a' => 1, 'b' => 2]);
        self::assertSame(['a' => 1, 'b' => 2], $c->all());
    }

    #[Test]
    public function hasKey(): void
    {
        $c = new HashCollection(['a' => 1, 'b' => null]);

        self::assertTrue($c->hasKey('a'));
        self::assertTrue($c->hasKey('b'));
        self::assertFalse($c->hasKey('c'));
    }

    #[Test]
    public function getReturnsNullForMissing(): void
    {
        $c = new HashCollection(['a' => 1]);

        self::assertSame(1, $c->get('a'));
        self::assertNull($c->get('missing'));
    }

    #[Test]
    public function set(): void
    {
        $c = new HashCollection();
        $c->set('key', 'value');

        self::assertSame('value', $c->get('key'));
    }

    #[Test]
    public function addUsesClassNameAsKey(): void
    {
        $c   = new HashCollection();
        $obj = new \stdClass();

        self::assertTrue($c->add($obj));
        self::assertSame($obj, $c->get(\stdClass::class));
    }

    #[Test]
    public function addReplacesSameClass(): void
    {
        $c      = new HashCollection();
        $first  = new \stdClass();
        $second = new \stdClass();

        $c->add($first);
        $c->add($second);

        self::assertCount(1, $c);
        self::assertSame($second, $c->get(\stdClass::class));
    }

    #[Test]
    public function remove(): void
    {
        $c = new HashCollection(['a' => 1, 'b' => 2]);

        self::assertSame(1, $c->remove('a'));
        self::assertNull($c->remove('a'));
        self::assertFalse($c->hasKey('a'));
    }

    #[Test]
    public function countElements(): void
    {
        self::assertCount(0, new HashCollection());
        self::assertCount(2, new HashCollection(['a' => 1, 'b' => 2]));
    }

    #[Test]
    public function arrayAccess(): void
    {
        $c = new HashCollection(['a' => 1]);

        self::assertTrue(isset($c['a']));
        self::assertSame(1, $c['a']);

        $c['b'] = 2;
        self::assertSame(2, $c['b']);

        unset($c['a']);
        self::assertFalse(isset($c['a']));
    }

    #[Test]
    public function offsetSetWithoutKeyAddsObject(): void
    {
        $c   = new HashCollection();
        $obj = new \stdClass();

        $c[] = $obj;

        self::assertSame($obj, $c->get(\stdClass::class));
    }

    #[Test]
    public function emptyState(): void
    {
        self::assertTrue((new HashCollection())->isEmpty());
        self::assertFalse((new HashCollection(['a' => 1]))->isEmpty());
    }

    #[Test]
    public function contains(): void
    {
        $obj = new \stdClass();
        $c   = new HashCollection(['a' => $obj, 'b' => 2]);

        self::assertTrue($c->contains($obj));
        self::assertTrue($c->contains(2));
        self::assertFalse($c->contains('2'));
        self::assertFalse($c->contains(new \stdClass()));
    }

    #[Test]
    public function clear(): void
    {
        $c = new HashCollection(['a' => 1, 'b' => 2]);
        $c->clear();

        self::assertTrue($c->isEmpty());
    }

    #[Test]
    public function find(): void
    {
        $c = new HashCollection(['a' => 1, 'b' => 2, 'c' => 3]);

        self::assertSame(2, $c->find(static fn($key, $element) => $element === 2));
        self::assertSame(3, $c->find(static fn($key, $element) => $key === 'c'));
        self::assertNull($c->find(static fn($key, $element) => $element === 99));
    }

    #[Test]
    public function serializesToJson(): void
    {
        self::assertSame('{"a":1}', json_encode(new HashCollection(['a' => 1])));
        self::assertSame('[]', json_encode(new HashCollection()));
        self::assertSame(['a' => 1], (new HashCollection(['a' => 1]))->toArray());
        self::assertInstanceOf(Arrayable::class, new HashCollection());
        self::assertInstanceOf(\JsonSerializable::class, new HashCollection());
    }

    #[Test]
    public function iteratesOverElements(): void
    {
        $c = new HashCollection(
            [
                'a' => 1,
                'b' => 2,
            ]
        );

        $seen = [];
        foreach ($c as $key => $value) {
            $seen[$key] = $value;
        }

        self::assertSame(
            [
                'a' => 1,
                'b' => 2,
            ],
            $seen
        );
        self::assertInstanceOf(\IteratorAggregate::class, $c);
    }

    #[Test]
    public function keysAndValues(): void
    {
        $c = new HashCollection(
            [
                'a' => 1,
                'b' => 2,
            ]
        );

        self::assertSame(
            [
                'a',
                'b',
            ],
            $c->keys()
        );
        self::assertSame(
            [
                1,
                2,
            ],
            $c->values()
        );
    }

    #[Test]
    public function mapReceivesValueAndKey(): void
    {
        $c = new HashCollection(
            [
                'a' => 1,
                'b' => 2,
            ]
        );

        self::assertSame(
            [
                'a' => 'a1',
                'b' => 'b2',
            ],
            $c->map(static fn($value, $key) => $key . $value)->all()
        );
        self::assertSame([], (new HashCollection())->map(static fn($v) => $v)->all());
    }

    #[Test]
    public function filterPreservesKeys(): void
    {
        $c = new HashCollection(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        self::assertSame(
            [
                'b' => 2,
                'c' => 3,
            ],
            $c->filter(static fn($value) => $value > 1)->all()
        );
        self::assertSame(['a' => 1], $c->filter(static fn($value, $key) => $key === 'a')->all());
    }

    #[Test]
    public function eachStopsOnFalse(): void
    {
        $c    = new HashCollection(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );
        $seen = [];

        $result = $c->each(
            static function ($value, $key) use (&$seen) {
                $seen[] = $key;

                return $value < 2;
            }
        );

        self::assertSame($c, $result);
        self::assertSame(
            [
                'a',
                'b',
            ],
            $seen
        );
    }

    #[Test]
    public function reduceAndImplode(): void
    {
        $c = new HashCollection(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        self::assertSame(6, $c->reduce(static fn($carry, $value) => (int)$carry + $value, 0));
        self::assertSame('1,2,3', $c->implode(','));
        self::assertNull((new HashCollection())->reduce(static fn($carry, $value) => $value));
    }

    #[Test]
    public function appendingWithoutAKeyRequiresAnObject(): void
    {
        $c = new HashCollection();

        $obj = new \stdClass();
        $c[] = $obj;
        self::assertSame($obj, $c->get(\stdClass::class));

        // a scalar used to reach the private add() and fail with a bare TypeError
        $this->expectException(InvalidParamException::class);
        $c[] = 'scalar';
    }
}
