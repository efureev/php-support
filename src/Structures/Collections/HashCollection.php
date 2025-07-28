<?php

declare(strict_types=1);

namespace Php\Support\Structures\Collections;

use ArrayAccess;
use Closure;
use Countable;

use function array_key_exists;
use function count;
use function in_array;

/**
 * @template T
 * @template TO of object
 */
class HashCollection implements ArrayAccess, Countable
{
    /**
     * @param array<string, T> $elements
     */
    public function __construct(protected array $elements = [])
    {
    }

    /**
     * Gets a native PHP array of the elements.
     *
     * @return array<string, T>
     */
    public function all(): array
    {
        return $this->elements;
    }

    /**
     * Checks whether the collection contains an element with the specified key/index.
     *
     * @param string $key The key/index to check for.
     *
     * @return bool TRUE if the collection contains an element with the specified key/index,
     *              FALSE otherwise.
     */
    public function hasKey(string $key): bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * Gets the element at the specified key/index.
     *
     * @param string $key The key/index of the element to retrieve.
     *
     * @return T|null
     */
    public function get(string $key): mixed
    {
        return $this->elements[$key] ?? null;
    }


    /**
     * Sets an element in the collection at the specified key/index.
     *
     * @param string $key The key/index of the element to set.
     * @param T $value The element to set.
     */
    public function set(string $key, mixed $value): void
    {
        $this->elements[$key] = $value;
    }


    /**
     * Adds an element at the end of the collection.
     *
     * @phpstan-param TO $element The element to add.
     */
    public function add(object $element): bool
    {
        $this->elements[$element::class] = $element;

        return true;
    }

    /**
     * Removes the element at the specified index from the collection.
     *
     * @param string $key The key/index of the element to remove.
     *
     * @return T|null The removed element or NULL, if the collection did not contain the element.
     */
    public function remove(string $key): mixed
    {
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @param string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->hasKey($offset);
    }

    /**
     * @param string $offset
     *
     * @return T|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * @param string|null $offset
     * @param T $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!isset($offset)) {
            $this->add($value);

            return;
        }

        $this->set($offset, $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * Checks whether the collection is empty (contains no elements).
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * Checks whether an element is contained in the collection.
     * This is an O(n) operation, where n is the size of the collection.
     *
     * @param TMaybeContained $element The element to search for.
     *
     * @return bool TRUE if the collection contains the element, FALSE otherwise.
     * @phpstan-return (TMaybeContained is T ? bool : false)
     *
     * @template TMaybeContained
     */
    public function contains(mixed $element): bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * Clears the collection, removing all elements.
     */
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * Returns the first element of this collection that satisfies the predicate $func.
     *
     * @param Closure(string, T):bool $func The predicate.
     *
     * @return null|T The first element respecting the predicate, null if no element respects the predicate.
     */
    public function find(Closure $func): mixed
    {
        return array_find($this->elements, fn($element, $key) => $func($key, $element));
    }
}
