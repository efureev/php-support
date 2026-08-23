<?php

declare(strict_types=1);

namespace Php\Support\Structures\Collections;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Interfaces\Arrayable;
use Traversable;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function in_array;
use function implode;

/**
 * @template T
 * @template TO of object
 * @implements ArrayAccess<string, T>
 * @implements IteratorAggregate<string, T>
 * @implements Arrayable<string, T>
 *
 * @phpstan-consistent-constructor
 */
class HashCollection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Arrayable
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
     * Gets a native PHP array representation of the collection.
     *
     * @return array<string, T>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * @return array<string, T>
     */
    public function jsonSerialize(): array
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
        if (isset($offset)) {
            $this->set($offset, $value);

            return;
        }

        // $collection[] = $value derives the key from the element's class, so it needs an object.
        // It used to reach add() unchecked and fail with a bare TypeError from a private method.
        if (!is_object($value)) {
            throw new InvalidParamException(
                sprintf(
                    'Appending without a key needs an object (its class becomes the key), %s given; use set()',
                    get_debug_type($value)
                ),
                'value'
            );
        }

        $this->add($value);
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

    /**
     * Creates a new instance from the given elements.
     *
     * Provided for derived classes that need different construction semantics.
     *
     * @param array<string, mixed> $elements
     *
     * @return static
     */
    protected function createFrom(array $elements): static
    {
        return new static($elements);
    }

    /**
     * @return Traversable<string, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Gets all keys of the collection.
     *
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * Gets all values of the collection.
     *
     * @return T[]
     */
    public function values(): array
    {
        return array_values($this->elements);
    }

    /**
     * Applies the given function to each element and returns a new collection.
     *
     * @template U
     * @param Closure(T, string):U $func
     *
     * @return static<U, TO>
     */
    public function map(Closure $func): static
    {
        $keys = array_keys($this->elements);
        $map  = array_map($func, $this->elements, $keys);

        return $this->createFrom(array_combine($keys, $map));
    }

    /**
     * Returns all elements that satisfy the predicate. Keys are preserved.
     *
     * @param null|Closure(T, string):bool $func
     *
     * @return static<T, TO>
     */
    public function filter(?Closure $func = null): static
    {
        return $this->createFrom(array_filter($this->elements, $func, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * Applies the given function to each element. Returning false stops the iteration.
     *
     * @param callable(T, string):mixed $func
     */
    public function each(callable $func): static
    {
        foreach ($this->elements as $key => $element) {
            if ($func($element, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Reduces the collection to a single value.
     *
     * @template TReturn
     * @param Closure(TReturn|null, T, string):TReturn $func
     * @param TReturn|null $initial
     *
     * @return TReturn|null
     */
    public function reduce(Closure $func, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($this->elements as $key => $element) {
            $result = $func($result, $element, $key);
        }

        return $result;
    }

    /**
     * Concatenates the elements with the given glue.
     */
    public function implode(string $glue = ''): string
    {
        return implode($glue, $this->elements);
    }
}
