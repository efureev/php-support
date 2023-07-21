<?php

declare(strict_types=1);

namespace Php\Support\Structures\Collections;

use ArrayIterator;
use Closure;
use Php\Support\Helpers\Arr;
use Stringable;
use Traversable;

use function array_chunk;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_reduce;
use function array_search;
use function array_slice;
use function array_values;
use function arsort;
use function asort;
use function count;
use function current;
use function end;
use function in_array;
use function is_array;
use function is_object;
use function key;
use function next;
use function property_exists;
use function reset;
use function spl_object_hash;
use function uasort;

/**
 * @psalm-template TKey of array-key
 * @psalm-template T
 * @template-implements Collection<TKey,T>
 *
 * @psalm-consistent-constructor
 */
class ArrayCollection implements Collection, Stringable
{
    /**
     * @var array
     * @psalm-var array<TKey,T>
     */
    protected array $elements = [];

    /**
     * @param array $elements
     * @psalm-param array<TKey,T> $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     *
     * @return Traversable<int|string, mixed>
     * @psalm-return Traversable<TKey, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * @param TKey $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->containsKey($offset);
    }

    /**
     * @param TKey $offset
     * @return T|null
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * @param int|string|null $offset
     * @param T $value
     * @psalm-param TKey|null $offset
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
     * @param TKey $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * {@inheritDoc}
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function containsKey(int|string $key): bool
    {
        return isset($this->elements[$key]) || array_key_exists($key, $this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function get(int|string $key): mixed
    {
        return $this->elements[$key] ?? null;
    }


    /**
     * {@inheritDoc}
     */
    public function set(int|string $key, mixed $value): void
    {
        $this->elements[$key] = $value;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress InvalidPropertyAssignmentValue
     */
    public function add(mixed $element): bool
    {
        $this->elements[] = $element;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function remove(int|string $key): mixed
    {
        if (!isset($this->elements[$key]) && !array_key_exists($key, $this->elements)) {
            return null;
        }

        $removed = $this->elements[$key];
        unset($this->elements[$key]);

        return $removed;
    }


    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return empty($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function getKeys(): array
    {
        return array_keys($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function getValues(): array
    {
        return array_values($this->elements);
    }


    /**
     * {@inheritDoc}
     *
     * @template TMaybeContained
     */
    public function contains(mixed $element): bool
    {
        return in_array($element, $this->elements, true);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param Closure(T):U $func
     *
     * @return static
     * @psalm-return static<TKey, U>
     *
     * @psalm-template U
     */
    public function map(Closure $func): static
    {
        return $this->createFrom(array_map($func, $this->elements));
    }

    /**
     * Map the values into a new class.
     *
     * @param string $class
     * @return static
     */
    public function mapInto(string $class): static
    {
        return $this->map(static fn($value) => new $class($value));
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param null|Closure(T,TKey):U $func
     *
     * @return static
     * @psalm-return static<TKey, U>
     *
     * @psalm-template U
     */
    public function mapByKey(string $keyName, ?string $valueName = null, ?Closure $func = null): static
    {
        $result = [];
        foreach ($this->elements as $ind => $element) {
            if ($valueName === null) {
                $value = $element;
            } else {
                $value = $func ? $func($element, $ind) : $this->getProperty($element, $valueName);
            }
            $result[$this->getProperty($element, $keyName)] = $value;
        }
        return $this->createFrom($result);
    }

    private function getProperty(mixed $target, string|int $keyName, bool $throwOnMiss = true): mixed
    {
        return match (true) {
            is_array($target) || $target instanceof \ArrayAccess
            => $throwOnMiss ? $target[$keyName] : ($target[$keyName] ?? null),
            is_object($target)
            => $throwOnMiss ? $target->$keyName : (property_exists($target, $keyName) ? $target->$keyName : null),
        };
    }

    /**
     * {@inheritDoc}
     *
     * @return static
     * @psalm-return static<TKey,T>
     */
    public function filter(Closure $func = null): static
    {
        return $this->createFrom(array_filter($this->elements, $func, ARRAY_FILTER_USE_BOTH));
    }

    public function whereInstanceOf(string|array $type): static
    {
        return $this->filter(
            static function ($value) use ($type) {
                foreach ((array)$type as $classType) {
                    if ($value instanceof $classType) {
                        return true;
                    }
                }

                return false;
            }
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return static
     * @psalm-return static<TKey,T>
     */
    public function reject(Closure $callback): static
    {
        return $this->filter(static fn($value, $key) => !$callback($value, $key));
    }

    /**
     * {@inheritDoc}
     */
    public function each(callable $func): static
    {
        foreach ($this as $key => $item) {
            if ($func($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function transform(Closure $func): static
    {
        $this->elements = array_map($func, $this->elements);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function merge(mixed $items): static
    {
        return $this->createFrom(array_merge($this->elements, Arr::toArray($items)));
    }

    /**
     * Creates a new instance from the specified elements.
     *
     * This method is provided for derived classes to specify how a new
     * instance should be created when constructor semantics have changed.
     *
     * @param array $elements Elements.
     * @psalm-param array<K,V> $elements
     *
     * @return static
     * @psalm-return static<K,V>
     *
     * @psalm-template K of array-key
     * @psalm-template V
     */
    protected function createFrom(array $elements): static
    {
        return new static($elements);
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): void
    {
        $this->elements = [];
    }

    /**
     * {@inheritDoc}
     */
    public function removeElement(mixed $element): bool
    {
        $key = array_search($element, $this->elements, true);

        if ($key === false) {
            return false;
        }

        unset($this->elements[$key]);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function first(): mixed
    {
        return reset($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function last(): mixed
    {
        return end($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function key(): int|string|null
    {
        return key($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function current(): mixed
    {
        return current($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): mixed
    {
        return next($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function slice(int $offset, ?int $length = null): array
    {
        return array_slice($this->elements, $offset, $length, true);
    }

    /**
     * {@inheritDoc}
     */
    public function exists(Closure $func): bool
    {
        foreach ($this->elements as $key => $element) {
            if ($func($key, $element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function partition(Closure $func): array
    {
        $matches = $noMatches = [];

        foreach ($this->elements as $key => $element) {
            if ($func($key, $element)) {
                $matches[$key] = $element;
            } else {
                $noMatches[$key] = $element;
            }
        }

        return [
            $this->createFrom($matches),
            $this->createFrom($noMatches),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function testForAll(Closure $func): bool
    {
        foreach ($this->elements as $key => $element) {
            if (!$func($key, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param TMaybeContained $element
     *
     * @return string|int|false
     * @psalm-return (TMaybeContained is T ? TKey|false : false)
     *
     * @template TMaybeContained
     */
    public function indexOf(mixed $element): string|int|bool
    {
        return array_search($element, $this->elements, true);
    }

    /**
     * {@inheritDoc}
     */
    public function findFirst(Closure $func): mixed
    {
        foreach ($this->elements as $key => $element) {
            if ($func($key, $element)) {
                return $element;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function reduce(Closure $func, mixed $initial = null): mixed
    {
        return array_reduce($this->elements, $func, $initial);
    }

    /**
     * Collapse the collection of items into a single array.
     *
     *
     * @return static
     * @psalm-return static<int,T>
     */
    public function collapse(): static
    {
        return $this->createFrom(Arr::collapse($this->elements));
    }

    /**
     * Push an element onto the beginning of the collection.
     *
     * @param T $value
     * @param TKey $key
     * @return static
     */
    public function prepend(mixed $value, $key = null): static
    {
        $this->elements = Arr::prepend($this->elements, ...func_get_args());

        return $this;
    }


    /**
     * Push one or more elements onto the end of the collection.
     *
     * @param T ...$values
     * @return static
     */
    public function push(...$values): static
    {
        foreach ($values as $value) {
            $this->elements[] = $value;
        }

        return $this;
    }

    /**
     * Reverse elements order.
     *
     * @return static
     */
    public function reverse(): static
    {
        return $this->createFrom(array_reverse($this->elements, true));
    }

    /**
     * Chunk the collection into chunks of the given size.
     *
     * @param int $size
     *
     * @return static<int, static>
     */
    public function chunk(int $size): static
    {
        if ($size <= 0) {
            return $this->createFrom([]);
        }

        $chunks = [];

        foreach (array_chunk($this->elements, $size, true) as $chunk) {
            $chunks[] = $this->createFrom($chunk);
        }

        return $this->createFrom($chunks);
    }

    /**
     * Sort through each item with a callback.
     *
     * @param (callable(T, T): int)|null|int $func
     *
     * @return static
     */
    public function sort(callable|int|null $func = null): static
    {
        $items = $this->elements;

        $func && is_callable($func)
            ? uasort($items, $func)
            : asort($items, $func ?? SORT_REGULAR);

        return $this->createFrom($items);
    }


    /**
     * Sort items in descending order.
     *
     * @param int $options
     * @return static
     */
    public function sortDesc(int $options = SORT_REGULAR): static
    {
        $items = $this->elements;

        arsort($items, $options);

        return $this->createFrom($items);
    }

    /**
     * Sort the collection using the given callback.
     *
     * @param array<array-key, (callable(T, T): mixed)|(callable(T, TKey): mixed)|string|array{string, string}>|(callable(T, TKey): mixed)|string $callback
     * @param int $options
     * @param bool $descending
     * @return static
     */
    public function sortBy(array|string|callable $callback, int $options = SORT_REGULAR, bool $descending = false)
    {
        if (is_array($callback) && !is_callable($callback)) {
            return $this->sortByMany($callback);
        }

        $results = [];

        if (is_callable($callback)) {
            // First we will loop through the items and get the comparator from a callback
            // function which we were given. Then, we will sort the returned values and
            // grab all the corresponding values for the sorted keys from this array.
            foreach ($this->elements as $key => $value) {
                $results[$key] = $callback($value, $key);
            }
        }
        $descending ? arsort($results, $options)
            : asort($results, $options);

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->elements[$key];
        }

        return $this->createFrom($results);
    }

    /**
     * Sort the collection using multiple comparisons.
     *
     * @param array<array-key, (callable(T, T): mixed)|(callable(T, TKey): mixed)|string|array{string, string}> $comparisons
     * @return static
     */
    protected function sortByMany(array $comparisons = []): static
    {
        $items = $this->elements;

        uasort(
            $items,
            function ($a, $b) use ($comparisons) {
                foreach ($comparisons as $comparison) {
                    $comparison = (array)$comparison;

                    $prop = $comparison[0];

                    $ascending = Arr::get($comparison, 1, true) === true ||
                        Arr::get($comparison, 1, true) === 'asc';

                    if (!is_string($prop) && is_callable($prop)) {
                        $result = $prop($a, $b);
                    } else {
                        $values = [
                            dataGet($a, $prop),
                            dataGet($b, $prop),
                        ];

                        if (!$ascending) {
                            $values = array_reverse($values);
                        }

                        $result = $values[0] <=> $values[1];
                    }

                    if ($result === 0) {
                        continue;
                    }

                    return $result;
                }
            }
        );

        return $this->createFrom($items);
    }

    /**
     * Sort the collection keys.
     *
     * @param int $options
     * @param bool $descending
     * @return static
     */
    public function sortKeys(int $options = SORT_REGULAR, bool $descending = false): static
    {
        $items = $this->elements;

        $descending ? krsort($items, $options) : ksort($items, $options);

        return $this->createFrom($items);
    }

    public function __toString(): string
    {
        return self::class . '@' . spl_object_hash($this);
    }
}
