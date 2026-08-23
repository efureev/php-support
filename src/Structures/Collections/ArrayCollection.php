<?php

declare(strict_types=1);

namespace Php\Support\Structures\Collections;

use ArrayIterator;
use Closure;
use JsonSerializable;
use Php\Support\Exceptions\InvalidParamException;
use Php\Support\Exceptions\MissingPropertyException;
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Json;
use Php\Support\Interfaces\Arrayable;
use Stringable;
use Traversable;

use function array_chunk;
use function is_numeric;
use function min;
use function max;
use function implode;
use function array_sum;
use function array_intersect;
use function array_diff;
use function array_combine;
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
use function is_callable;
use function is_object;
use function key;
use function next;
use function property_exists;
use function reset;
use function spl_object_hash;
use function uasort;

/**
 * @phpstan-template TKey of array-key
 * @phpstan-template T
 * @template-implements Collection<TKey,T>
 * @template-implements Arrayable<TKey,T>
 *
 * @phpstan-type SortComparator (callable(T, T): mixed)|(callable(T, TKey): mixed)|string
 * @phpstan-type SortComparisons array<array-key, SortComparator|array{string, string}>
 *
 * @phpstan-consistent-constructor
 */
class ArrayCollection implements Collection, Stringable, JsonSerializable, Arrayable
{
    /**
     * @var array
     * @phpstan-var array<TKey,T>
     */
    protected array $elements = [];

    /**
     * @param array<TKey,T>|Collection<TKey,T> $elements
     */
    public function __construct(array|Collection $elements = [])
    {
        if ($elements instanceof Collection) {
            $this->elements = $elements->all();
        } else {
            $this->elements = $elements;
        }
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
     * @return array<TKey, T>
     */
    public function jsonSerialize(): array
    {
        return $this->elements;
    }

    /**
     * {@inheritDoc}
     *
     * @return Traversable<int|string, mixed>
     * @phpstan-return Traversable<TKey, T>
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
     * @phpstan-param TKey|null $offset
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
     * @phpstan-param Closure(T):U $func
     *
     * @return static
     * @phpstan-return static<TKey, U>
     *
     * @phpstan-template U
     */
    public function map(Closure $func): static
    {
        $keys = array_keys($this->elements);
        $map  = array_map($func, $this->elements, $keys);

        return $this->createFrom(array_combine($keys, $map));
    }

    /**
     * Map the values into a new class.
     *
     * @param string $class
     * @param mixed ...$params
     * @return static
     */
    public function mapInto(string $class, mixed ...$params): static
    {
        return $this->map(static fn($value) => new $class($value, ...$params));
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-param null|Closure(T,TKey):U $func
     *
     * @return static
     * @phpstan-return static<TKey, U>
     *
     * @phpstan-template U
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
        if (is_array($target) || $target instanceof \ArrayAccess) {
            $exists = is_array($target)
                ? array_key_exists($keyName, $target)
                : $target->offsetExists($keyName);

            if ($exists) {
                return $target[$keyName];
            }

            return $throwOnMiss ? throw new MissingPropertyException((string)$keyName) : null;
        }

        if (is_object($target)) {
            if (property_exists($target, (string)$keyName) || isset($target->$keyName)) {
                return $target->$keyName;
            }

            return $throwOnMiss ? throw new MissingPropertyException((string)$keyName) : null;
        }

        return $throwOnMiss
            ? throw new InvalidParamException('Unsupported target type for property extraction')
            : null;
    }

    /**
     * {@inheritDoc}
     *
     * @return static
     * @phpstan-return static<TKey,T>
     */
    public function filter(?Closure $func = null): static
    {
        return $this->createFrom(array_filter($this->elements, $func, ARRAY_FILTER_USE_BOTH));
    }

    /**
     * @param class-string|class-string[] $type
     */
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
     * @phpstan-return static<TKey,T>
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
    public function merge(iterable $items): static
    {
        return $this->createFrom(array_merge($this->elements, Arr::toArray($items)));
    }

    /**
     * Creates a new instance from the specified elements.
     *
     * This method is provided for derived classes to specify how a new
     * instance should be created when constructor semantics have changed.
     *
     * @param array<K,V>|Collection<K,V> $elements Elements.
     *
     * @return static
     * @phpstan-return static<K,V>
     *
     * @phpstan-template K of array-key
     * @phpstan-template V
     */
    protected function createFrom(array|Collection $elements): static
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
     * @phpstan-param TMaybeContained $element
     *
     * @return string|int|false
     * @phpstan-return (TMaybeContained is T ? TKey|false : false)
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
     * @phpstan-return static<int,T>
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
            throw new InvalidParamException("Chunk size must be a positive integer, $size given", 'size');
        }

        $chunks = [];

        foreach (array_chunk($this->elements, $size, true) as $chunk) {
            $chunks[] = $this->createFrom($chunk);
        }

        return $this->createFrom($chunks);
    }

    public function clone(): static
    {
        return $this->createFrom($this);
    }

    /**
     * Push all the given items onto the collection.
     *
     * @param iterable<array-key, T> $source
     */
    public function concat(iterable $source): static
    {
        $result = $this->clone();

        foreach ($source as $item) {
            $result->push($item);
        }

        return $result;
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
     * @param SortComparisons|SortComparator $callback
     * @param int $options
     * @param bool $descending
     * @return static
     */
    public function sortBy(array|string|callable $callback, int $options = SORT_REGULAR, bool $descending = false)
    {
        if (is_array($callback) && !is_callable($callback)) {
            return $this->sortByMany($callback);
        }

        $callback = $this->valueRetriever($callback);

        $results = [];

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // grab all the corresponding values for the sorted keys from this array.
        foreach ($this->elements as $key => $value) {
            $results[$key] = $callback($value, $key);
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
     * @param SortComparisons $comparisons
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

                return 0;
            }
        );

        return $this->createFrom($items);
    }

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
    public function random(callable|int|null $number = null, bool $preserveKeys = false): mixed
    {
        if ($number === null) {
            return Arr::random($this->elements);
        }

        if (is_callable($number)) {
            return $this->createFrom(Arr::random($this->elements, $number($this), $preserveKeys));
        }

        return $this->createFrom(Arr::random($this->elements, $number, $preserveKeys));
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

    protected function useAsCallable(mixed $value): bool
    {
        return !is_string($value) && is_callable($value);
    }

    protected function valueRetriever(mixed $value): callable
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return fn($item) => Arr::get($item, $value);
    }

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param (callable(T, TKey): array-key)|string[]|string $groupBy
     * @param bool $preserveKeys
     * @phpstan-return static<array-key, static<array-key, T>>
     * @return static<int|string, static<int|string, T>>
     */
    public function groupBy(callable|array|string $groupBy, bool $preserveKeys = false): static
    {
        if (is_array($groupBy) && !$this->useAsCallable($groupBy)) {
            $nextGroups = $groupBy;

            $groupBy = array_shift($nextGroups);
        }

        $groupBy = $this->valueRetriever($groupBy);

        $results = [];

        foreach ($this->elements as $key => $value) {
            $groupKeys = $groupBy($value, $key);

            if (!is_array($groupKeys)) {
                $groupKeys = [$groupKeys];
            }

            foreach ($groupKeys as $groupKey) {
                $groupKey = match (true) {
                    is_bool($groupKey) => (int)$groupKey,
                    $groupKey instanceof \BackedEnum => $groupKey->value,
                    $groupKey instanceof \Stringable => (string)$groupKey,
                    default => $groupKey,
                };

                if (!array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = $this->createFrom([]);
                }

                $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
            }
        }

        $result = $this->createFrom($results);

        if (!empty($nextGroups)) {
            return $result->map(fn(Collection $item) => $item->groupBy($nextGroups, $preserveKeys));
        }

        return $result;
    }

    public function __toString(): string
    {
        return self::class . '@' . spl_object_hash($this);
    }

    /**
     * Get the values of a given key, optionally keyed by another one.
     *
     * @param string|int|null $value
     * @param string|int|null $key
     *
     * @return static<array-key, mixed>
     */
    public function pluck(string|int|null $value, string|int|null $key = null): static
    {
        return $this->createFrom(Arr::pluck($this->elements, $value, $key));
    }

    /**
     * Sum the collection, optionally mapping each element first.
     *
     * @param (callable(T, TKey): (int|float))|string|null $callback
     */
    public function sum(callable|string|null $callback = null): int|float
    {
        $values = $this->resolveNumbers($callback);

        return array_sum($values);
    }

    /**
     * Average of the collection, or null when it is empty.
     *
     * @param (callable(T, TKey): (int|float))|string|null $callback
     */
    public function avg(callable|string|null $callback = null): int|float|null
    {
        $values = $this->resolveNumbers($callback);

        return $values === [] ? null : array_sum($values) / count($values);
    }

    /**
     * Smallest value of the collection, or null when it is empty.
     *
     * @param (callable(T, TKey): mixed)|string|null $callback
     */
    public function min(callable|string|null $callback = null): mixed
    {
        $values = $this->resolveValues($callback);

        return $values === [] ? null : min($values);
    }

    /**
     * Largest value of the collection, or null when it is empty.
     *
     * @param (callable(T, TKey): mixed)|string|null $callback
     */
    public function max(callable|string|null $callback = null): mixed
    {
        $values = $this->resolveValues($callback);

        return $values === [] ? null : max($values);
    }

    /**
     * @param (callable(T, TKey): mixed)|string|null $callback
     *
     * @return array<int, int|float>
     */
    private function resolveNumbers(callable|string|null $callback): array
    {
        return array_map(static fn($value) => is_numeric($value) ? $value + 0 : 0, $this->resolveValues($callback));
    }

    /**
     * @param (callable(T, TKey): mixed)|string|null $callback
     *
     * @return array<int, mixed>
     */
    private function resolveValues(callable|string|null $callback): array
    {
        if ($callback === null) {
            return array_values($this->elements);
        }

        $retriever = $this->valueRetriever($callback);
        $result    = [];

        foreach ($this->elements as $key => $element) {
            $result[] = $retriever($element, $key);
        }

        return $result;
    }

    /**
     * Remove duplicate values. Keys of the first occurrence are preserved.
     *
     * @param (callable(T, TKey): mixed)|string|null $callback
     * @param bool $strict Compare types as well.
     *
     * @return static<TKey, T>
     */
    public function unique(callable|string|null $callback = null, bool $strict = false): static
    {
        $retriever = $callback === null ? null : $this->valueRetriever($callback);
        $seen      = [];
        $result    = [];

        foreach ($this->elements as $key => $element) {
            $marker = $retriever === null ? $element : $retriever($element, $key);

            if (in_array($marker, $seen, $strict)) {
                continue;
            }

            $seen[]       = $marker;
            $result[$key] = $element;
        }

        return $this->createFrom($result);
    }

    /**
     * Key the collection by a field or a callback.
     *
     * @param (callable(T, TKey): array-key)|string|int $keyBy
     *
     * @return static<array-key, T>
     */
    public function keyBy(callable|string|int $keyBy): static
    {
        return $this->createFrom(Arr::keyBy($this->elements, $keyBy));
    }

    /**
     * Flatten a multi-dimensional collection into a single level.
     *
     * @return static<int, mixed>
     */
    public function flatten(int|float $depth = INF): static
    {
        return $this->createFrom(Arr::flatten($this->elements, $depth));
    }

    /**
     * Reset the keys to a sequential range.
     *
     * @return static<int, T>
     */
    public function values(): static
    {
        return $this->createFrom(array_values($this->elements));
    }

    /**
     * Concatenate the elements, optionally plucking a field first.
     */
    public function implode(string $glue = '', string|int|null $field = null): string
    {
        $values = $field === null ? $this->elements : Arr::pluck($this->elements, $field);

        return implode($glue, array_map(static fn($value) => (string)$value, $values));
    }

    /**
     * Pass the collection to the callback and return the collection.
     *
     * @param callable(static): mixed $callback
     */
    public function tap(callable $callback): static
    {
        $callback($this);

        return $this;
    }

    /**
     * Pass the collection to the callback and return its result.
     *
     * @template TPipe
     * @param callable(static): TPipe $callback
     *
     * @return TPipe
     */
    public function pipe(callable $callback): mixed
    {
        return $callback($this);
    }

    /**
     * Apply the callback when the value is truthy.
     *
     * @param callable(static, mixed): mixed $callback
     * @param null|callable(static, mixed): mixed $default
     */
    public function when(mixed $value, callable $callback, ?callable $default = null): static
    {
        if ($value) {
            return $callback($this, $value) ?? $this;
        }

        if ($default !== null) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }

    /**
     * Apply the callback when the value is falsy.
     *
     * @param callable(static, mixed): mixed $callback
     * @param null|callable(static, mixed): mixed $default
     */
    public function unless(mixed $value, callable $callback, ?callable $default = null): static
    {
        return $this->when(!$value, $callback, $default);
    }

    /**
     * Elements that are not present in the given items. Keys are preserved.
     *
     * @param iterable<array-key, T> $items
     *
     * @return static<TKey, T>
     */
    public function diff(iterable $items): static
    {
        return $this->createFrom(array_diff($this->elements, Arr::toArray($items)));
    }

    /**
     * Elements that are also present in the given items. Keys are preserved.
     *
     * @param iterable<array-key, T> $items
     *
     * @return static<TKey, T>
     */
    public function intersect(iterable $items): static
    {
        return $this->createFrom(array_intersect($this->elements, Arr::toArray($items)));
    }

    /**
     * Take the first (or, for a negative value, the last) $limit elements.
     *
     * @return static<TKey, T>
     */
    public function take(int $limit): static
    {
        if ($limit < 0) {
            return $this->createFrom(array_slice($this->elements, $limit, null, true));
        }

        return $this->createFrom(array_slice($this->elements, 0, $limit, true));
    }

    /**
     * Skip the first $count elements.
     *
     * @return static<TKey, T>
     */
    public function skip(int $count): static
    {
        return $this->createFrom(array_slice($this->elements, $count, null, true));
    }

    /**
     * JSON representation of the collection.
     */
    public function toJson(int $options = 320): ?string
    {
        return Json::encode($this->elements, $options);
    }
}
