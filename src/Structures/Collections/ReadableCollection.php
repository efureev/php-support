<?php

declare(strict_types=1);

namespace Php\Support\Structures\Collections;

use Closure;
use Countable;
use IteratorAggregate;

/**
 * @template TKey of array-key
 * @template TValue
 * @template-extends IteratorAggregate<TKey, TValue>
 */
interface ReadableCollection extends Countable, IteratorAggregate
{
    /**
     * Checks whether an element is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element The element to search for.
     * @phpstan-param TMaybeContained $element
     *
     * @return bool TRUE if the collection contains the element, FALSE otherwise.
     * @phpstan-return (TMaybeContained is TValue ? bool : false)
     *
     * @template TMaybeContained
     */
    public function contains(mixed $element): bool;

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return bool TRUE if the collection is empty, FALSE otherwise.
     */
    public function isEmpty(): bool;

    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param string|int $key The key/index to check for.
     * @phpstan-param TKey $key
     *
     * @return bool TRUE if the collection contains an element with the specified key/index,
     *              FALSE otherwise.
     */
    public function containsKey(string|int $key): bool;

    /**
     * Gets the element at the specified key/index.
     *
     * @param string|int $key The key/index of the element to retrieve.
     * @phpstan-param TKey $key
     *
     * @return mixed
     * @phpstan-return ?TValue
     */
    public function get(string|int $key): mixed;

    /**
     * Gets all keys/indices of the collection.
     *
     * @return int[]|string[] The keys/indices of the collection, in the order of the corresponding
     *               elements in the collection.
     * @phpstan-return TKey[]
     */
    public function getKeys(): array;

    /**
     * Gets all values of the collection.
     *
     * @return array The values of all elements in the collection, in the
     *                 order they appear in the collection.
     * @phpstan-return TValue[]
     */
    public function getValues(): array;

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return array
     * @phpstan-return array<TKey,TValue>
     */
    public function toArray(): array;

    /**
     * Gets a native PHP array of the elements.
     *
     * @return array
     * @phpstan-return array<TKey,TValue>
     */
    public function all(): array;

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     * @phpstan-return TValue|false
     */
    public function first(): mixed;

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     * @phpstan-return TValue|false
     */
    public function last(): mixed;

    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string|null
     * @phpstan-return ?TKey
     */
    public function key(): int|string|null;

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @phpstan-return TValue|false
     */
    public function current(): mixed;

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @phpstan-return TValue|false
     */
    public function next(): mixed;

    /**
     * Extracts a slice of $length elements starting at position $offset from the Collection.
     *
     * If $length is null it returns all elements from $offset to the end of the Collection.
     * Keys have to be preserved by this method. Calling this method will only return the
     * selected slice and NOT change the elements contained in the collection slice is called on.
     *
     * @param int $offset The offset to start from.
     * @param int|null $length The maximum number of elements to return, or null for no limit.
     *
     * @return array
     * @phpstan-return array<TKey,TValue>
     */
    public function slice(int $offset, ?int $length = null): array;

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param Closure $func The predicate.
     * @phpstan-param Closure(TKey, TValue):bool $func
     *
     * @return bool TRUE if the predicate is TRUE for at least one element, FALSE otherwise.
     */
    public function exists(Closure $func): bool;

    /**
     * Returns all the elements of this collection that satisfy the predicate $func.
     * The order of the elements is preserved.
     *
     * @param ?Closure $func The predicate used for filtering.
     * @phpstan-param null|Closure(TValue, TKey):bool $func
     *
     * @return ReadableCollection<TKey, TValue> A collection with the results of the filter operation.
     * @phpstan-return ReadableCollection<TKey, TValue>
     */
    public function filter(?Closure $func = null): ReadableCollection;

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param Closure $callback The predicate used for filtering.
     * @phpstan-param Closure(TValue, TKey):bool $callback
     *
     * @return ReadableCollection<mixed> A collection with the results of the filter operation.
     * @phpstan-return ReadableCollection<TKey, TValue>
     */
    public function reject(Closure $callback): ReadableCollection;

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @phpstan-param Closure(TValue):U $func
     *
     * @return ReadableCollection<mixed>
     * @phpstan-return ReadableCollection<TKey, U>
     *
     * @phpstan-template U
     */
    public function map(Closure $func): ReadableCollection;

    /**
     * Returns a new collection with Key = $keyName  and the elements returned by the function if it exists.
     *
     * @param string $keyName
     * @param ?string $valueName
     * @phpstan-param null|Closure(TValue,TKey):U $func
     *
     * @return ReadableCollection<int|string, mixed>
     * @phpstan-return ReadableCollection<TKey, U>
     *
     * @phpstan-template U
     */
    public function mapByKey(string $keyName, ?string $valueName = null, ?Closure $func = null): ReadableCollection;

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param Closure $func The predicate on which to partition.
     * @phpstan-param Closure(TKey, TValue):bool $func
     *
     * @return ReadableCollection<mixed>[] An array with two elements. The first element contains the collection
     *                      of elements where the predicate returned TRUE, the second element
     *                      contains the collection of elements where the predicate returned FALSE.
     * @phpstan-return array{0: ReadableCollection<TKey, TValue>, 1: ReadableCollection<TKey, TValue>}
     */
    public function partition(Closure $func): array;

    /**
     * Tests whether the given predicate $func holds for all elements of this collection.
     *
     * @param Closure $func The predicate.
     * @phpstan-param Closure(TKey, TValue):bool $func
     *
     * @return bool TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.
     */
    public function testForAll(Closure $func): bool;

    /**
     * Applies the given function to each element of the Collection. Returns the same Collection.
     *
     * @param callable $func The predicate.
     * @phpstan-param callable(TKey, TValue):bool $func
     */
    public function each(callable $func): static;

    /**
     * Transform each item in the collection using a callback.
     *
     * @param Closure $func The predicate.
     * @phpstan-param Closure(TKey, TValue):void $func
     */
    public function transform(Closure $func): static;


    /**
     * Merge the collection with the given items.
     *
     * @param iterable<TKey, TValue> $items
     */
    public function merge(iterable $items): static;

    /**
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element The element to search for.
     * @phpstan-param TMaybeContained $element
     *
     * @return int|string|bool The key/index of the element or FALSE if the element was not found.
     * @phpstan-return (TMaybeContained is TValue ? TKey|false : false)
     *
     * @template TMaybeContained
     */
    public function indexOf(mixed $element): string|int|bool;

    /**
     * Returns the first element of this collection that satisfies the predicate $func.
     *
     * @param Closure $func The predicate.
     * @phpstan-param Closure(TKey, TValue):bool $func
     *
     * @return mixed The first element respecting the predicate,
     *               null if no element respects the predicate.
     * @phpstan-return ?TValue
     */
    public function findFirst(Closure $func): mixed;

    /**
     * Applies iteratively the given function to each element in the collection,
     * to reduce the collection to a single value.
     *
     * @phpstan-param Closure(TReturn|TInitial|null, TValue):(TInitial|TReturn) $func
     * @phpstan-param TInitial|null $initial
     *
     * @return mixed
     * @phpstan-return TReturn|TInitial|null
     *
     * @phpstan-template TReturn
     * @phpstan-template TInitial
     */
    public function reduce(Closure $func, mixed $initial = null): mixed;
}
