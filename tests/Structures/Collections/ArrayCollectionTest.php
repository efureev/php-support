<?php

declare(strict_types=1);

namespace Php\Support\Tests\Structures\Collections;

use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Exceptions\MissingPropertyException;
use Php\Support\Interfaces\Arrayable;
use Php\Support\Structures\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ArrayCollectionTest extends TestCase
{
    #[Test]
    public function constructFromArrayAndCollection(): void
    {
        $c = new ArrayCollection(['a' => 1, 'b' => 2]);
        self::assertSame(['a' => 1, 'b' => 2], $c->toArray());
        self::assertSame(['a' => 1, 'b' => 2], $c->all());

        $copy = new ArrayCollection($c);
        self::assertSame(['a' => 1, 'b' => 2], $copy->all());
    }

    #[Test]
    public function countAndIsEmpty(): void
    {
        self::assertTrue((new ArrayCollection())->isEmpty());
        self::assertSame(0, (new ArrayCollection())->count());

        $c = new ArrayCollection([1, 2, 3]);
        self::assertFalse($c->isEmpty());
        self::assertCount(3, $c);
    }

    #[Test]
    public function getSetAddRemove(): void
    {
        $c = new ArrayCollection(['a' => 1]);

        self::assertSame(1, $c->get('a'));
        self::assertNull($c->get('missing'));

        $c->set('b', 2);
        self::assertSame(2, $c->get('b'));

        self::assertTrue($c->add(3));
        self::assertSame(3, $c->get(0));

        self::assertSame(2, $c->remove('b'));
        self::assertNull($c->remove('b'));
        self::assertFalse($c->containsKey('b'));
    }

    #[Test]
    public function containsKeyAndContains(): void
    {
        $c = new ArrayCollection(['a' => 1, 'b' => null]);

        self::assertTrue($c->containsKey('a'));
        self::assertTrue($c->containsKey('b'));
        self::assertFalse($c->containsKey('c'));

        self::assertTrue($c->contains(1));
        self::assertFalse($c->contains('1'));
        self::assertFalse($c->contains(2));
    }

    #[Test]
    public function keysAndValues(): void
    {
        $c = new ArrayCollection(['a' => 1, 'b' => 2]);

        self::assertSame(['a', 'b'], $c->getKeys());
        self::assertSame([1, 2], $c->getValues());
    }

    #[Test]
    public function arrayAccess(): void
    {
        $c = new ArrayCollection(['a' => 1]);

        self::assertTrue(isset($c['a']));
        self::assertSame(1, $c['a']);

        $c['b'] = 2;
        self::assertSame(2, $c['b']);

        $c[] = 3;
        self::assertSame(3, $c[0]);

        unset($c['a']);
        self::assertFalse(isset($c['a']));
    }

    #[Test]
    public function map(): void
    {
        $c      = new ArrayCollection([1, 2, 3]);
        $mapped = $c->map(static fn($value) => $value * 2);

        self::assertSame([2, 4, 6], $mapped->all());
        // original is untouched
        self::assertSame([1, 2, 3], $c->all());
    }

    #[Test]
    public function mapReceivesKey(): void
    {
        $c      = new ArrayCollection(['a' => 1, 'b' => 2]);
        $mapped = $c->map(static fn($value, $key) => "$key:$value");

        self::assertSame(['a' => 'a:1', 'b' => 'b:2'], $mapped->all());
    }

    #[Test]
    public function mapInto(): void
    {
        $c      = new ArrayCollection(['x', 'y']);
        $mapped = $c->mapInto(ValueWrapper::class);

        self::assertInstanceOf(ValueWrapper::class, $mapped->get(0));
        self::assertSame('x', $mapped->get(0)->value);
        self::assertSame('y', $mapped->get(1)->value);
    }

    #[Test]
    public function mapByKey(): void
    {
        $c = new ArrayCollection(
            [
                [
                    'id'   => 10,
                    'name' => 'a',
                ],
                [
                    'id'   => 20,
                    'name' => 'b',
                ],
            ]
        );

        self::assertSame([10 => 'a', 20 => 'b'], $c->mapByKey('id', 'name')->all());

        // without valueName whole element is kept
        self::assertSame(
            [
                10 => [
                    'id'   => 10,
                    'name' => 'a',
                ],
                20 => [
                    'id'   => 20,
                    'name' => 'b',
                ],
            ],
            $c->mapByKey('id')->all()
        );

        // with callback for value
        self::assertSame(
            [
                10 => 'A',
                20 => 'B',
            ],
            $c->mapByKey('id', 'name', static fn($el) => strtoupper($el['name']))->all()
        );
    }

    #[Test]
    public function getPropertyThrowsOnUnsupportedTarget(): void
    {
        $c = new ArrayCollection(['scalar-value']);

        $this->expectException(InvalidParamException::class);
        $c->mapByKey('id');
    }

    #[Test]
    public function getPropertyFromObject(): void
    {
        $obj       = new \stdClass();
        $obj->id   = 5;
        $obj->name = 'foo';

        $c = new ArrayCollection([$obj]);

        self::assertSame([5 => 'foo'], $c->mapByKey('id', 'name')->all());
    }

    #[Test]
    public function filter(): void
    {
        $c        = new ArrayCollection([1, 2, 3, 4]);
        $filtered = $c->filter(static fn($value) => $value % 2 === 0);

        self::assertSame([1 => 2, 3 => 4], $filtered->all());
    }

    #[Test]
    public function filterWithoutCallbackRemovesFalsy(): void
    {
        $c = new ArrayCollection([0, 1, '', 'a', null, 2]);

        self::assertSame([1 => 1, 3 => 'a', 5 => 2], $c->filter()->all());
    }

    #[Test]
    public function whereInstanceOf(): void
    {
        $a = new \ArrayObject();
        $b = new \stdClass();
        $c = new ArrayCollection([$a, $b, 'string']);

        $result = $c->whereInstanceOf(\ArrayObject::class);
        self::assertSame([0 => $a], $result->all());

        $multi = $c->whereInstanceOf([\ArrayObject::class, \stdClass::class]);
        self::assertSame([0 => $a, 1 => $b], $multi->all());
    }

    #[Test]
    public function reject(): void
    {
        $c      = new ArrayCollection([1, 2, 3, 4]);
        $result = $c->reject(static fn($value) => $value % 2 === 0);

        self::assertSame([0 => 1, 2 => 3], $result->all());
    }

    #[Test]
    public function each(): void
    {
        $c    = new ArrayCollection([1, 2, 3]);
        $seen = [];
        $c->each(
            function ($item, $key) use (&$seen) {
                $seen[$key] = $item;
            }
        );

        self::assertSame([1, 2, 3], $seen);
    }

    #[Test]
    public function eachStopsOnFalse(): void
    {
        $c    = new ArrayCollection([1, 2, 3, 4]);
        $seen = [];
        $c->each(
            function ($item) use (&$seen) {
                if ($item === 3) {
                    return false;
                }
                $seen[] = $item;
                return true;
            }
        );

        self::assertSame([1, 2], $seen);
    }

    #[Test]
    public function transformMutatesInPlace(): void
    {
        $c      = new ArrayCollection([1, 2, 3]);
        $result = $c->transform(static fn($value) => $value + 1);

        self::assertSame($c, $result);
        self::assertSame([2, 3, 4], $c->all());
    }

    #[Test]
    public function merge(): void
    {
        $c      = new ArrayCollection(['a' => 1, 'b' => 2]);
        $merged = $c->merge(['b' => 3, 'c' => 4]);

        self::assertSame(['a' => 1, 'b' => 3, 'c' => 4], $merged->all());
    }

    #[Test]
    public function clear(): void
    {
        $c = new ArrayCollection([1, 2, 3]);
        $c->clear();

        self::assertTrue($c->isEmpty());
    }

    #[Test]
    public function removeElement(): void
    {
        $c = new ArrayCollection([1, 2, 3]);

        self::assertTrue($c->removeElement(2));
        self::assertSame([0 => 1, 2 => 3], $c->all());
        self::assertFalse($c->removeElement(99));
    }

    #[Test]
    public function firstLast(): void
    {
        $c = new ArrayCollection([10, 20, 30]);

        self::assertSame(10, $c->first());
        self::assertSame(30, $c->last());
    }

    #[Test]
    public function internalIteration(): void
    {
        $c = new ArrayCollection(['a' => 1, 'b' => 2]);

        self::assertSame(1, $c->current());
        self::assertSame('a', $c->key());
        self::assertSame(2, $c->next());
    }

    #[Test]
    public function iteratorAggregate(): void
    {
        $c      = new ArrayCollection(['a' => 1, 'b' => 2]);
        $result = [];
        foreach ($c as $key => $value) {
            $result[$key] = $value;
        }

        self::assertSame(['a' => 1, 'b' => 2], $result);
    }

    #[Test]
    public function slice(): void
    {
        $c = new ArrayCollection([1, 2, 3, 4, 5]);

        self::assertSame([1 => 2, 2 => 3], $c->slice(1, 2));
    }

    #[Test]
    public function exists(): void
    {
        $c = new ArrayCollection([1, 2, 3]);

        self::assertTrue($c->exists(static fn($key, $element) => $element === 2));
        self::assertFalse($c->exists(static fn($key, $element) => $element === 99));
    }

    #[Test]
    public function partition(): void
    {
        $c = new ArrayCollection([1, 2, 3, 4]);
        [
            $even,
            $odd,
        ]  = $c->partition(static fn($key, $element) => $element % 2 === 0);

        self::assertSame([1 => 2, 3 => 4], $even->all());
        self::assertSame([0 => 1, 2 => 3], $odd->all());
    }

    #[Test]
    public function testForAll(): void
    {
        $c = new ArrayCollection([2, 4, 6]);

        self::assertTrue($c->testForAll(static fn($key, $element) => $element % 2 === 0));
        self::assertFalse($c->testForAll(static fn($key, $element) => $element > 2));
    }

    #[Test]
    public function indexOf(): void
    {
        $c = new ArrayCollection(['a' => 1, 'b' => 2]);

        self::assertSame('b', $c->indexOf(2));
        self::assertFalse($c->indexOf(99));
    }

    #[Test]
    public function findFirst(): void
    {
        $c = new ArrayCollection([1, 2, 3, 4]);

        self::assertSame(2, $c->findFirst(static fn($key, $element) => $element > 1));
        self::assertNull($c->findFirst(static fn($key, $element) => $element > 99));
    }

    #[Test]
    public function reduce(): void
    {
        $c = new ArrayCollection([1, 2, 3, 4]);

        self::assertSame(10, $c->reduce(static fn($carry, $item) => $carry + $item, 0));
    }

    #[Test]
    public function collapse(): void
    {
        $c = new ArrayCollection([[1, 2], [3, 4], [5]]);

        self::assertSame([1, 2, 3, 4, 5], $c->collapse()->all());
    }

    #[Test]
    public function prepend(): void
    {
        $c = new ArrayCollection([2, 3]);
        $c->prepend(1);

        self::assertSame([1, 2, 3], $c->all());
    }

    #[Test]
    public function push(): void
    {
        $c = new ArrayCollection([1]);
        $c->push(2, 3);

        self::assertSame([1, 2, 3], $c->all());
    }

    #[Test]
    public function reverse(): void
    {
        $c = new ArrayCollection(['a' => 1, 'b' => 2, 'c' => 3]);

        self::assertSame(['c' => 3, 'b' => 2, 'a' => 1], $c->reverse()->all());
    }

    #[Test]
    public function chunk(): void
    {
        $c      = new ArrayCollection([1, 2, 3, 4, 5]);
        $chunks = $c->chunk(2);

        self::assertCount(3, $chunks);
        self::assertSame([1, 2], $chunks->get(0)->all());
        self::assertSame([2 => 3, 3 => 4], $chunks->get(1)->all());
        self::assertSame([4 => 5], $chunks->get(2)->all());
    }

    #[Test]
    public function chunkRejectsNonPositiveSize(): void
    {
        $c = new ArrayCollection([1, 2, 3]);

        $this->expectException(InvalidParamException::class);
        $c->chunk(0);
    }

    #[Test]
    public function clone(): void
    {
        $c     = new ArrayCollection([1, 2, 3]);
        $clone = $c->clone();

        self::assertNotSame($c, $clone);
        self::assertSame($c->all(), $clone->all());
    }

    #[Test]
    public function concat(): void
    {
        $c      = new ArrayCollection([1, 2]);
        $result = $c->concat([3, 4]);

        self::assertSame([1, 2, 3, 4], $result->all());
        // source collection is not mutated
        self::assertSame([1, 2], $c->all());
    }

    #[Test]
    public function sort(): void
    {
        $c = new ArrayCollection([3, 1, 2]);

        self::assertSame([1 => 1, 2 => 2, 0 => 3], $c->sort()->all());

        $byCallback = (new ArrayCollection([3, 1, 2]))->sort(static fn($a, $b) => $b <=> $a);
        self::assertSame([3, 2, 1], array_values($byCallback->all()));
    }

    #[Test]
    public function sortDesc(): void
    {
        $c = new ArrayCollection([1, 3, 2]);

        self::assertSame([3, 2, 1], array_values($c->sortDesc()->all()));
    }

    #[Test]
    public function sortByStringField(): void
    {
        $c = new ArrayCollection(
            [
                ['n' => 3],
                ['n' => 1],
                ['n' => 2],
            ]
        );

        $sorted = $c->sortBy('n');
        self::assertSame([1, 2, 3], array_column($sorted->all(), 'n'));
    }

    #[Test]
    public function sortByCallback(): void
    {
        $c = new ArrayCollection([['n' => 3], ['n' => 1], ['n' => 2]]);

        $sorted = $c->sortBy(static fn($item) => $item['n']);
        self::assertSame([1, 2, 3], array_column($sorted->all(), 'n'));
    }

    #[Test]
    public function sortByDescending(): void
    {
        $c = new ArrayCollection([['n' => 3], ['n' => 1], ['n' => 2]]);

        $sorted = $c->sortBy('n', SORT_REGULAR, true);
        self::assertSame([3, 2, 1], array_column($sorted->all(), 'n'));
    }

    #[Test]
    public function sortByMany(): void
    {
        $c = new ArrayCollection(
            [
                [
                    'a' => 1,
                    'b' => 2,
                ],
                [
                    'a' => 1,
                    'b' => 1,
                ],
                [
                    'a' => 0,
                    'b' => 5,
                ],
            ]
        );

        $sorted = $c->sortBy([['a', 'asc'], ['b', 'asc']]);
        $values = array_values($sorted->all());

        self::assertSame(['a' => 0, 'b' => 5], $values[0]);
        self::assertSame(['a' => 1, 'b' => 1], $values[1]);
        self::assertSame(['a' => 1, 'b' => 2], $values[2]);
    }

    #[Test]
    public function sortKeys(): void
    {
        $c = new ArrayCollection(['c' => 3, 'a' => 1, 'b' => 2]);

        self::assertSame(['a' => 1, 'b' => 2, 'c' => 3], $c->sortKeys()->all());
        self::assertSame(['c' => 3, 'b' => 2, 'a' => 1], $c->sortKeys(SORT_REGULAR, true)->all());
    }

    #[Test]
    public function random(): void
    {
        $c = new ArrayCollection([1, 2, 3, 4, 5]);

        $single = $c->random();
        self::assertContains($single, [1, 2, 3, 4, 5]);

        $many = $c->random(2);
        self::assertInstanceOf(ArrayCollection::class, $many);
        self::assertCount(2, $many);

        $byCallback = $c->random(static fn(ArrayCollection $col) => 3);
        self::assertCount(3, $byCallback);
    }

    #[Test]
    public function groupByField(): void
    {
        $c = new ArrayCollection(
            [
                [
                    'type' => 'a',
                    'v'    => 1,
                ],
                [
                    'type' => 'b',
                    'v'    => 2,
                ],
                [
                    'type' => 'a',
                    'v'    => 3,
                ],
            ]
        );

        $grouped = $c->groupBy('type');

        self::assertCount(2, $grouped);
        self::assertCount(2, $grouped->get('a'));
        self::assertCount(1, $grouped->get('b'));
    }

    #[Test]
    public function groupByCallback(): void
    {
        $c = new ArrayCollection([1, 2, 3, 4]);

        $grouped = $c->groupBy(static fn($value) => $value % 2 === 0 ? 'even' : 'odd');

        self::assertSame([1, 3], array_values($grouped->get('odd')->all()));
        self::assertSame([2, 4], array_values($grouped->get('even')->all()));
    }

    #[Test]
    public function toStringContainsClassName(): void
    {
        $c = new ArrayCollection();

        self::assertStringContainsString(ArrayCollection::class, (string)$c);
    }

    #[Test]
    public function serializesToJson(): void
    {
        self::assertSame('[1,2]', json_encode(new ArrayCollection([1, 2])));
        self::assertSame('{"a":1}', json_encode(new ArrayCollection(['a' => 1])));
        self::assertSame('[]', json_encode(new ArrayCollection()));
        self::assertInstanceOf(Arrayable::class, new ArrayCollection());
        self::assertInstanceOf(\JsonSerializable::class, new ArrayCollection());
    }

    #[Test]
    public function mapByKeyThrowsOnMissingKey(): void
    {
        $c = new ArrayCollection([['a' => 1]]);

        $this->expectException(MissingPropertyException::class);
        $c->mapByKey('nope');
    }

    #[Test]
    public function mapByKeyThrowsOnMissingObjectProperty(): void
    {
        $obj     = new \stdClass();
        $obj->id = 5;

        $c = new ArrayCollection([$obj]);

        $this->expectException(MissingPropertyException::class);
        $c->mapByKey('nope');
    }

    #[Test]
    public function randomUsesCreateFromForSubclasses(): void
    {
        $c = new class ([1, 2, 3, 4, 5]) extends ArrayCollection {
        };

        self::assertInstanceOf($c::class, $c->random(2));
    }
}
