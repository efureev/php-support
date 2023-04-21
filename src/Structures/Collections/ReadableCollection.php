<?php

declare(strict_types=1);

namespace Php\Support\Structures\Collections;

use Closure;
use Countable;
use IteratorAggregate;
use Php\Support\Interfaces\Arrayable;

/**
 * @psalm-template TKey of array-key
 * @template-covariant T
 * @template-extends IteratorAggregate<TKey, T>
 */
interface ReadableCollection extends Countable, IteratorAggregate
{
    /**
     * Checks whether an element is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param mixed $element The element to search for.
     * @psalm-param TMaybeContained $element
     *
     * @return bool TRUE if the collection contains the element, FALSE otherwise.
     * @psalm-return (TMaybeContained is T ? bool : false)
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
     * @psalm-param TKey $key
     *
     * @return bool TRUE if the collection contains an element with the specified key/index,
     *              FALSE otherwise.
     */
    public function containsKey(string|int $key): bool;

    /**
     * Gets the element at the specified key/index.
     *
     * @param string|int $key The key/index of the element to retrieve.
     * @psalm-param TKey $key
     *
     * @return mixed
     * @psalm-return T|null
     */
    public function get(string|int $key): mixed;

    /**
     * Gets all keys/indices of the collection.
     *
     * @return int[]|string[] The keys/indices of the collection, in the order of the corresponding
     *               elements in the collection.
     * @psalm-return list<TKey>
     */
    public function getKeys(): array;

    /**
     * Gets all values of the collection.
     *
     * @return mixed[] The values of all elements in the collection, in the
     *                 order they appear in the collection.
     * @psalm-return list<T>
     */
    public function getValues(): array;

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return mixed[]
     * @psalm-return array<TKey,T>
     */
    public function toArray(): array;

    /**
     * Gets a native PHP array of the elements.
     *
     * @return mixed[]
     * @psalm-return array<TKey,T>
     */
    public function all(): array;

    /**
     * Sets the internal iterator to the first element in the collection and returns this element.
     *
     * @return mixed
     * @psalm-return T|false
     */
    public function first(): mixed;

    /**
     * Sets the internal iterator to the last element in the collection and returns this element.
     *
     * @return mixed
     * @psalm-return T|false
     */
    public function last(): mixed;

    /**
     * Gets the key/index of the element at the current iterator position.
     *
     * @return int|string|null
     * @psalm-return TKey|null
     */
    public function key(): int|string|null;

    /**
     * Gets the element of the collection at the current iterator position.
     *
     * @return mixed
     * @psalm-return T|false
     */
    public function current(): mixed;

    /**
     * Moves the internal iterator position to the next element and returns this element.
     *
     * @return mixed
     * @psalm-return T|false
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
     * @return mixed[]
     * @psalm-return array<TKey,T>
     */
    public function slice(int $offset, int|null $length = null): array;

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param Closure $func The predicate.
     * @psalm-param Closure(TKey, T):bool $func
     *
     * @return bool TRUE if the predicate is TRUE for at least one element, FALSE otherwise.
     */
    public function exists(Closure $func): bool;

    /**
     * Returns all the elements of this collection that satisfy the predicate $func.
     * The order of the elements is preserved.
     *
     * @param null|Closure $func The predicate used for filtering.
     * @psalm-param null|Closure(T, TKey):bool $func
     *
     * @return ReadableCollection<mixed> A collection with the results of the filter operation.
     * @psalm-return ReadableCollection<TKey, T>
     */
    public function filter(Closure $func = null): ReadableCollection;

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param Closure $callback The predicate used for filtering.
     * @psalm-param Closure(T, TKey):bool $callback
     *
     * @return ReadableCollection<mixed> A collection with the results of the filter operation.
     * @psalm-return ReadableCollection<TKey, T>
     */
    public function reject(Closure $callback): ReadableCollection;

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @psalm-param Closure(T):U $func
     *
     * @return ReadableCollection<mixed>
     * @psalm-return ReadableCollection<TKey, U>
     *
     * @psalm-template U
     */
    public function map(Closure $func): ReadableCollection;

    /**
     * Returns a new collection with Key = $keyName  and the elements returned by the function if it exists.
     *
     * @param string $keyName
     * @param ?string $valueName
     * @psalm-param null|Closure(T,TKey):U $func
     *
     * @return ReadableCollection<int|string, mixed>
     * @psalm-return ReadableCollection<TKey, U>
     *
     * @psalm-template U
     */
    public function mapByKey(string $keyName, ?string $valueName = null, ?Closure $func = null): ReadableCollection;

    /**
     * Partitions this collection in two collections according to a predicate.
     * Keys are preserved in the resulting collections.
     *
     * @param Closure $func The predicate on which to partition.
     * @psalm-param Closure(TKey, T):bool $func
     *
     * @return ReadableCollection<mixed>[] An array with two elements. The first element contains the collection
     *                      of elements where the predicate returned TRUE, the second element
     *                      contains the collection of elements where the predicate returned FALSE.
     * @psalm-return array{0: ReadableCollection<TKey, T>, 1: ReadableCollection<TKey, T>}
     */
    public function partition(Closure $func): array;

    /**
     * Tests whether the given predicate $func holds for all elements of this collection.
     *
     * @param Closure $func The predicate.
     * @psalm-param Closure(TKey, T):bool $func
     *
     * @return bool TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.
     */
    public function testForAll(Closure $func): bool;

    /**
     * Applies the given function to each element of the Collection. Returns the same Collection.
     *
     * @param Closure $func The predicate.
     * @psalm-param Closure(TKey, T):bool $func
     */
    public function each(Closure $func): bool;

    /**
     * Transform each item in the collection using a callback.
     *
     * @param Closure $func The predicate.
     * @psalm-param Closure(TKey, T):void $func
     */
    public function transform(Closure $func): static;


    /**
     * Merge the collection with the given items.
     *
     * @param \iterable|Arrayable $items
     * @return static
     */
    // @phpstan-ignore-next-line
    public function merge(mixed $items): static;

    /**
     * Gets the index/key of a given element. The comparison of two elements is strict,
     * that means not only the value but also the type must match.
     * For objects this means reference equality.
     *
     * @param mixed $element The element to search for.
     * @psalm-param TMaybeContained $element
     *
     * @return int|string|bool The key/index of the element or FALSE if the element was not found.
     * @psalm-return (TMaybeContained is T ? TKey|false : false)
     *
     * @template TMaybeContained
     */
    public function indexOf(mixed $element): string|int|bool;

    /**
     * Returns the first element of this collection that satisfies the predicate $func.
     *
     * @param Closure $func The predicate.
     * @psalm-param Closure(TKey, T):bool $func
     *
     * @return mixed The first element respecting the predicate,
     *               null if no element respects the predicate.
     * @psalm-return T|null
     */
    public function findFirst(Closure $func): mixed;

    /**
     * Applies iteratively the given function to each element in the collection,
     * so as to reduce the collection to a single value.
     *
     * @psalm-param Closure(TReturn|TInitial|null, T):(TInitial|TReturn) $func
     * @psalm-param TInitial|null $initial
     *
     * @return mixed
     * @psalm-return TReturn|TInitial|null
     *
     * @psalm-template TReturn
     * @psalm-template TInitial
     */
    public function reduce(Closure $func, mixed $initial = null): mixed;
}
