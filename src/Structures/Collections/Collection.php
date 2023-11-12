<?php

declare(strict_types=1);

namespace Php\Support\Structures\Collections;

use ArrayAccess;

/**
 * The missing (SPL) Collection/Array/OrderedMap interface.
 *
 * A Collection resembles the nature of a regular PHP array. That is,
 * it is essentially an <b>ordered map</b> that can also be used
 * like a list.
 *
 * A Collection has an internal iterator just like a PHP array. In addition,
 * a Collection can be iterated with external iterators, which is preferable.
 * To use an external iterator simply use the foreach language construct to
 * iterate over the collection (which calls {@link getIterator()} internally) or
 * explicitly retrieve an iterator though {@link getIterator()} which can then be
 * used to iterate over the collection.
 * You can not rely on the internal iterator of the collection being at a certain
 * position unless you explicitly positioned it before. Prefer iteration with
 * external iterators.
 *
 * @author Doctrine
 *
 * @psalm-template TKey of array-key
 * @psalm-template T
 * @template-extends ReadableCollection<TKey, T>
 * @template-extends ArrayAccess<TKey, T>
 */
interface Collection extends ReadableCollection, ArrayAccess
{
    /**
     * Adds an element at the end of the collection.
     *
     * @param mixed $element The element to add.
     * @psalm-param T $element
     *
     * @return bool
     */
    public function add(mixed $element): bool;

    /**
     * Clears the collection, removing all elements.
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Removes the element at the specified index from the collection.
     *
     * @param string|int $key The key/index of the element to remove.
     * @psalm-param TKey $key
     *
     * @return mixed The removed element or NULL, if the collection did not contain the element.
     * @psalm-return T|null
     */
    public function remove(string|int $key): mixed;

    /**
     * Removes the specified element from the collection, if it is found.
     *
     * @param mixed $element The element to remove.
     * @psalm-param T $element
     *
     * @return bool TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeElement(mixed $element): bool;

    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param string|int $key The key/index of the element to set.
     * @param mixed $value The element to set.
     * @psalm-param TKey $key
     * @psalm-param T $value
     *
     * @return void
     */
    public function set(string|int $key, mixed $value): void;

    /**
     * Push all the given items onto the collection.
     *
     * @param iterable<array-key, T> $source
     */
    public function concat(iterable $source): static;

    public function clone(): static;

    /**
     * Get one or a specified number of items randomly from the collection.
     *
     * @param (callable(self<TKey, T>): int)|int|null $number
     * @param bool $preserveKeys
     *
     * @return static<int, T>|T
     *
     * @throws \InvalidArgumentException
     */
    public function random(callable|int|null $number = null, bool $preserveKeys = false): mixed;

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param (callable(T, TKey): array-key)|array|string $groupBy
     * @param bool $preserveKeys
     * @psalm-param (callable(T, TKey): array-key)|array|string $groupBy
     */
    public function groupBy(callable|array|string $groupBy, bool $preserveKeys = false): static;
}
