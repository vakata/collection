<?php

namespace vakata\collection;

use ArrayAccess;
use ArrayObject;
use Countable;
use Iterator;
use RuntimeException;

/**
 * @template T
 * @implements \Iterator<T>
 * @implements \ArrayAccess<int|string,T>
 */
class Collection implements Iterator, ArrayAccess, Countable
{
    /**
     * @var ?ArrayObject<int|string,T>
     */
    protected ?ArrayObject $array = null;
    protected Iterator $iterator;
    /**
     * @var array<array{0:string,1:callable}>
     */
    protected array $stack = [];
    protected int|string|null $key = null;
    /**
     * @var T
     */
    protected mixed $val = null;

    /**
     * @param  int|float  $low  start value
     * @param  int|float  $high end value
     * @param  int|float  $step increment
     * @return \Generator<int|float>
     */
    protected static function rangeGenerator($low, $high, $step = 1)
    {
        $k = -1;
        for ($i = $low; $i <= $high; $i += $step) {
            yield ++$k => $i;
        }
    }
    /**
     * Create a collection based on a range generator
     * @param  int|float  $low  start value
     * @param  int|float  $high end value
     * @param  int|float  $step increment
     * @return Collection<int|float>
     */
    public static function range($low, $high, $step = 1): Collection
    {
        return new self(static::rangeGenerator($low, $high, $step));
    }
    /**
     * A static alias of the __constructor
     * @param  iterable<T>  $input  Anything iterable
     * @return Collection<T>
     */
    public static function from(iterable $input): Collection
    {
        return new self($input);
    }
    /**
     * Create an instance
     * @param iterable<T> $input  Anything iterable
     */
    public function __construct(iterable $input = [])
    {
        if ($input instanceof self) {
            $this->array = new \ArrayObject($input->toArray());
            $this->iterator = $this->array->getIterator();
        } elseif ($input instanceof \Iterator) {
            $this->array = null;
            $this->iterator = $input;
        } elseif ($input instanceof \IteratorAggregate) {
            $this->array = new \ArrayObject(iterator_to_array($input));
            $this->iterator = $this->array->getIterator();
        } elseif (is_array($input)) {
            $this->array = new \ArrayObject($input);
            $this->iterator = $this->array->getIterator();
        } else {
            throw new RuntimeException('Invalid collection input');
        }
    }

    /**
     * @return ArrayObject<int|string,T>
     */
    protected function getArray(): ArrayObject
    {
        if (!isset($this->array)) {
            $this->array = new ArrayObject(iterator_to_array($this));
            $this->stack = [];
            $this->iterator = $this->array->getIterator();
        }
        return $this->array;
    }

    public function __clone()
    {
        $this->array = new ArrayObject(iterator_to_array($this));
        $this->stack = [];
        $this->iterator = $this->array->getIterator();
    }
    public function __toString(): string
    {
        return implode(', ', $this->toArray());
    }
    /**
     * @return array{data:array<int|string,mixed>}
     */
    public function __serialize(): array
    {
        return [ 'data' => $this->toArray() ];
    }
    /**
     * @param array{data:array<int|string,T>} $array
     */
    public function __unserialize($array): void
    {
        $this->array = new ArrayObject($array['data']);
        $this->stack = [];
        $this->iterator = $this->array->getIterator();
    }

    /**
     * Applies all pending operations
     * @return $this
     */
    public function squash(): static
    {
        if (count($this->stack) || !isset($this->array)) {
            $this->array = new ArrayObject(iterator_to_array($this));
            $this->stack = [];
            $this->iterator = $this->array->getIterator();
        }
        return $this;
    }
    /**
     * Get an actual array from the collection
     * @param  string|int|null $key optional key to extract
     * @param  string|int|null $val optional val to extract
     * @return array<int|string,mixed>
     */
    public function toArray($key = null, $val = null): array
    {
        if (isset($key)) {
            $this->pluckKey($key);
        }
        if (isset($val)) {
            $this->pluck($val);
        }
        return $this->squash()->getArray()->getArrayCopy();
    }
    /**
     * Gets the first value in the collection or null if empty
     * @return ?T
     */
    public function value(): mixed
    {
        foreach ($this as $v) {
            return $v;
        }
        return null;
    }

    // iterator
    public function key(): mixed
    {
        return $this->key;
    }
    /**
     * @return T
     */
    public function current(): mixed
    {
        return $this->val;
    }
    public function rewind(): void
    {
        $this->iterator->rewind();
    }
    public function next(): void
    {
        $this->iterator->next();
    }
    public function valid(): bool
    {
        while ($this->iterator->valid()) {
            $this->val = $this->iterator->current();
            $this->key = $this->iterator->key();
            $con = false;
            foreach ($this->stack as $action) {
                if ($action[0] === 'filter') {
                    if (!call_user_func($action[1], $this->val, $this->key, $this)) {
                        $con = true;
                        break;
                    }
                }
                if ($action[0] === 'map') {
                    $this->val = call_user_func($action[1], $this->val, $this->key, $this);
                }
                if ($action[0] === 'mapKey') {
                    $this->key = call_user_func($action[1], $this->val, $this->key, $this);
                }
            }
            if ($con) {
                $this->iterator->next();
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * @return ?T
     */
    public function offsetGet($offset): mixed
    {
        return $this->squash()->getArray()->offsetGet($offset);
    }
    public function offsetExists($offset): bool
    {
        return $this->squash()->getArray()->offsetExists($offset);
    }
    public function offsetUnset($offset): void
    {
        $this->squash()->getArray()->offsetUnset($offset);
    }
    /**
     * @param mixed $offset
     * @param T $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->squash()->getArray()->offsetSet($offset, $value);
    }
    /**
     * @param T $value
     * @return $this
     */
    public function add($value): static
    {
        $this->squash()->getArray()->append($value);
        return $this;
    }
    /**
     * @param T $value
     * @return $this
     */
    public function append($value): static
    {
        return $this->add($value);
    }
    /**
     * @param T $value
     * @return $this
     */
    public function remove($value): static
    {
        return $this->filter(function ($v) use ($value) { return $v !== $value; })->squash();
    }
    /**
     * Get the collection length
     * @return int
     */
    public function count(): int
    {
        if (count($this->stack)) {
            $this->squash();
        }
        if ($this->iterator instanceof \Countable) {
            return $this->iterator->count();
        }
        if (!isset($this->array)) {
            $this->squash();
        }
        return $this->getArray()->count();
    }

    // mutators
    /**
     * Filter values from the collection based on a predicate. The callback will receive the value, key and collection
     * @param  callable $iterator the predicate
     * @return $this
     */
    public function filter(callable $iterator): Collection
    {
        $this->stack[] = [ 'filter', $iterator ];
        return $this;
    }
    /**
     * Pass all values of the collection through a mutator callable, which will receive the value, key and collection
     * @param  callable $iterator the mutator
     * @return $this
     */
    public function map(callable $iterator): Collection
    {
        $this->stack[] = [ 'map', $iterator ];
        return $this;
    }
    /**
     * Pass all values of the collection through a key mutator callable, which will receive the value, key and collection
     * @param  callable $iterator the mutator
     * @return $this
     */
    public function mapKey(callable $iterator): Collection
    {
        $this->stack[] = [ 'mapKey', $iterator ];
        return $this;
    }
    /**
     * Clone the current collection and return it.
     * @return Collection<T>
     */
    public function clone(): Collection
    {
        return new self($this->toArray());
    }
    /**
     * Remove all falsy values from the collection (uses filter internally).
     * @return $this
     */
    public function compact(): Collection
    {
        return $this->filter(function ($v) {
            return !!$v;
        });
    }
    /**
     * Exclude all listed values from the collection (uses filter internally).
     * @param  iterable<T> $values the values to exclude
     * @return $this
     */
    public function difference($values): Collection
    {
        if (!is_array($values)) {
            $values = iterator_to_array($values);
        }
        $keys = array_keys($values);
        $isAssoc = $keys !== array_keys($keys);
        return $this->filter(function ($v, $k) use ($values, $isAssoc) {
            return $isAssoc ? 
                ($index = array_search($v, $values)) === false || $index !== $k :
                !in_array($v, $values, true);
        });
    }
    /**
     * Append more values to the collection
     * @param  iterable<T> $source the values to add
     * @return Collection<T>
     */
    public function extend($source): Collection
    {
        if (!is_array($source)) {
            $source = iterator_to_array($source);
        }
        return new self(array_merge($this->toArray(), $source));
    }
    /**
     * Append more values to the collection
     * @param  iterable<T> $source the values to add
     * @return Collection<T>
     */
    public function merge($source): Collection
    {
        return $this->extend($source);
    }
    /**
     * Perform a shallow flatten of the collection
     * @return Collection<T>
     */
    public function flatten(): Collection
    {
        $rslt = [];
        $temp = $this->toArray();
        foreach ($temp as $v) {
            $rslt = array_merge($rslt, is_array($v) ? $v : [$v]);
        }
        return new self($rslt);
    }
    /**
     * Group by a key (if a callable is used - return the value to group by)
     * @param  string|callable $iterator the key to group by
     * @return Collection<array<T>>
     */
    public function groupBy($iterator): Collection
    {
        /** @var array<int|string,array<T>> */
        $rslt = [];
        /** @var array<int|string,T> $temp */
        $temp = $this->toArray();
        foreach ($temp as $k => $v) {
            $rslt[is_string($iterator) ? (is_object($v) ? $v->{$iterator} : $v[$iterator]) : call_user_func($iterator, $v, $k)][] = $v;
        }
        return new self($rslt);
    }
    /**
     * Get the first X items from the collection
     * @param  int $count the number of items to include (defaults to 1)
     * @return Collection<T>
     */
    public function first(int $count = 1): Collection
    {
        $i = 0;
        $new = [];
        foreach ($this as $k => $v) {
            if (++$i > $count) {
                break;
            }
            $new[$k] = $v;
        }
        return new self($new);
    }
    /**
     * Get the first X items from the collection
     * @param  int $count the number of items to include (defaults to 1)
     * @return Collection<T>
     */
    public function head(int $count = 1): Collection
    {
        return $this->first($count);
    }
    /**
     * Get the last X items from the collection
     * @param  int $count the number of items to include (defaults to 1)
     * @return Collection<T>
     */
    public function last(int $count = 1): Collection
    {
        $new = $this->toArray();
        return new self(array_slice($new, $count * -1));
    }
    /**
     * Get the last X items from the collection
     * @param  int $count the number of items to include (defaults to 1)
     * @return Collection<T>
     */
    public function tail(int $count = 1): Collection
    {
        return $this->last($count);
    }
    /**
     * Get all but the last X items from the collection
     * @param  int $count the number of items to exclude (defaults to 1)
     * @return Collection<T>
     */
    public function initial(int $count = 1): Collection
    {
        $new = $this->toArray();
        return new self(array_slice($new, 0, $count * -1));
    }
    /**
     * Get all but the first X items from the collection
     * @param  int $count the number of items to exclude (defaults to 1)
     * @return Collection<T>
     */
    public function rest(int $count = 1): Collection
    {
        $new = $this->toArray();
        return new self(array_slice($new, $count));
    }
    /**
     * Execute a callable for each item in the collection (does not modify the collection)
     * @param callable(T, int|string, Collection<T>): void $iterator the callable to execute
     * @return $this
     */
    public function each(callable $iterator): Collection
    {
        foreach ($this as $k => $v) {
            call_user_func($iterator, $v, $k, $this);
        }
        return $this;
    }
    /**
     * Execute a callable for each item in the collection (does not modify the collection)
     * @param  callable $iterator the callable to execute
     * @return $this
     */
    public function invoke(callable $iterator): Collection
    {
        return $this->each($iterator);
    }
    /**
     * Get all the collection keys
     * @return Collection<int|string>
     */
    public function keys(): Collection
    {
        return $this->map(function ($v, $k) { return $k; })->values();
    }
    /**
     * Pluck a key for each object (uses map internally)
     * @param  string|int $key the key to extract
     * @return $this
     */
    public function pluckKey($key): Collection
    {
        return $this->mapKey(function ($v, $k) use ($key) {
            return is_object($v) ?
                (isset($v->{$key}) ? $v->{$key} : $k) :
                (isset($v[$key]) ? $v[$key] : $k);
        });
    }
    /**
     * Pluck a key / val pair for each object (uses map internally)
     * @param  string|int $key the key to extract
     * @param  string|int $val the val to extract
     * @return $this
     */
    public function pluckKeyVal($key, $val): Collection
    {
        return $this->pluckKey($key)->pluck($val);
    }
    /**
     * Pluck a value from each object (uses map internally)
     * @param  string|int $key the key to extract
     * @return $this
     */
    public function pluck($key): Collection
    {
        return $this->map(function ($v) use ($key) {
            return is_object($v) ?
                (isset($v->{$key}) ? $v->{$key} : null) :
                (isset($v[$key]) ? $v[$key] : null);
        });
    }
    /**
     * Intersect the collection with another iterable (uses filter internally)
     * @param  iterable<T> $values the data to intersect with
     * @return $this
     */
    public function intersection($values): Collection
    {
        if (!is_array($values)) {
            $values = iterator_to_array($values);
        }
        $keys = array_keys($values);
        $isAssoc = $keys !== array_keys($keys);
        return $this->filter(function ($v, $k) use ($values, $isAssoc) {
            return $isAssoc ? 
                array_search($v, $values) === $k :
                in_array($v, $values, true);
        });
    }
    /**
     * Reject values on a given predicate (opposite of filter)
     * @param  callable $iterator the predicate
     * @return $this
     */
    public function reject(callable $iterator): Collection
    {
        return $this->filter(function ($v, $k, $array) use ($iterator) {
            return !call_user_func($iterator, $v, $k, $array);
        });
    }
    /**
     * Shuffle the values in the collection
     * @return Collection<T>
     */
    public function shuffle(): Collection
    {
        $temp = $this->toArray();
        $keys = array_keys($temp);
        shuffle($keys);
        $rslt = [];
        foreach ($keys as $key) {
            $rslt[$key] = $temp[$key];
        }
        return new self($rslt);
    }
    /**
     * Sort the collection using a standard sorting function
     * @param  callable $iterator the sort function (must return -1, 0 or 1)
     * @return Collection<T>
     */
    public function sortBy(callable $iterator): Collection
    {
        $this->squash();
        $this->getArray()->uasort($iterator);
        return $this;
    }
    /**
     * Inspect the whole collection (as an array) mid-chain
     * @param  callable $iterator the callable to execute
     * @return $this
     */
    public function tap(callable $iterator): Collection
    {
        call_user_func($iterator, $this->toArray());
        return $this;
    }
    /**
     * Modify the whole collection (as an array) mid-chain
     * @param  callable $iterator the callable to execute
     * @return Collection
     */
    public function thru(callable $iterator): Collection
    {
        $temp = $this->toArray();
        $rslt = call_user_func($iterator, $temp);
        return new self($rslt);
    }
    /**
     * Leave only unique items in the collection
     * @return Collection<T>
     */
    public function unique(): Collection
    {
        $temp = $this->toArray();
        $rslt = [];
        foreach ($temp as $k => $v) {
            if (!in_array($v, $rslt, true)) {
                $rslt[$k] = $v;
            }
        }
        return new self($rslt);
    }
    /**
     * Get only the values of the collection
     * @return Collection<T>
     */
    public function values(): Collection
    {
        return new self(array_values($this->toArray()));
    }

    /**
     * @param T $v
     * @param array<string|int,mixed> $properties
     * @param bool $strict
     * @return bool
     */
    protected function whereCallback($v, $properties, $strict = true): bool
    {
        foreach ($properties as $key => $value) {
            $vv = is_object($v) ? (isset($v->{$key}) ? $v->{$key} : null) : (isset($v[$key]) ? $v[$key] : null);
            $negate = false;
            if (is_array($value) && count($value) === 1 && isset($value['not'])) {
                $value = $value['not'];
                $negate = true;
            }
            if (is_array($value) && isset($value['beg']) && strlen($value['beg']) && (!isset($value['end']) || !strlen($value['end']))) {
                $value = [ 'gte' => $value['beg'] ];
            }
            if (is_array($value) && isset($value['end']) && strlen($value['end']) && (!isset($value['beg']) || !strlen($value['beg']))) {
                $value = [ 'lte' => $value['end'] ];
            }
            if (is_array($value)) {
                if (isset($value['beg']) && isset($value['end'])) {
                    if ($vv < $value['beg'] || $vv > $value['end']) {
                        if (!$negate) {
                            return false;
                        }
                    } else {
                        if ($negate) {
                            return false;
                        }
                    }
                } elseif (isset($value['lt']) || isset($value['gt']) || isset($value['lte']) || isset($value['gte'])) {
                    if (isset($value['lt']) && $vv >= $value['lt']) {
                        if (!$negate) {
                            return false;
                        }
                    } else {
                        if ($negate) {
                            return false;
                        }
                    }
                    if (isset($value['gt']) && $vv <= $value['gt']) {
                        if (!$negate) {
                            return false;
                        }
                    } else {
                        if ($negate) {
                            return false;
                        }
                    }
                    if (isset($value['lte']) && $vv > $value['lte']) {
                        if (!$negate) {
                            return false;
                        }
                    } else {
                        if ($negate) {
                            return false;
                        }
                    }
                    if (isset($value['gte']) && $vv < $value['gte']) {
                        if (!$negate) {
                            return false;
                        }
                    } else {
                        if ($negate) {
                            return false;
                        }
                    }
                } else {
                    if (!in_array($vv, $value, $strict)) {
                        if (!$negate) {
                            return false;
                        }
                    } else {
                        if ($negate) {
                            return false;
                        }
                    }
                }
            } else {
                if (($strict && $vv !== $value) || (!$strict && $vv != $value)) {
                    if (!$negate) {
                        return false;
                    }
                } else {
                    if ($negate) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    /**
     * @param array<array<string|int,mixed>> $criteria
     * @return Collection<T>
     */
    public function whereAll(array $criteria): Collection
    {
        return $this->filter(function ($v) use ($criteria) {
            foreach ($criteria as $row) {
                if (!$this->whereCallback($v, $row)) {
                    return false;
                }
            }
            return true;
        });
    }
    /**
     * @param array<array<string|int,mixed>> $criteria
     * @return Collection<T>
     */
    public function whereAny(array $criteria): Collection
    {
        return $this->filter(function ($v) use ($criteria) {
            foreach ($criteria as $row) {
                if ($this->whereCallback($v, $row)) {
                    return true;
                }
            }
            return false;
        });
    }
    /**
     * Filter items from the collection using key => value pairs
     * @param  array<string|int,mixed>   $properties the key => value to check for in each item
     * @param  boolean $strict     should the comparison be strict
     * @return $this
     */
    public function where(array $properties, $strict = true): Collection
    {
        return $this->filter(function ($v) use ($properties, $strict) {
            return $this->whereCallback($v, $properties, $strict);
        });
    }
    /**
     * Exclude all listed values from the collection (uses filter internally).
     * @param  iterable<T> $values the values to exclude
     * @return $this
     */
    public function without($values): Collection
    {
        return $this->difference($values);
    }
    /**
     * Combine all the values from the collection with a key
     * @param iterable<int|string> $keys the keys to use
     * @return Collection<T>
     */
    public function zip($keys): Collection
    {
        if (!is_array($keys)) {
            $keys = iterator_to_array($keys);
        }
        return new self(array_combine($keys, $this->toArray()));
    }
    /**
     * Reverse the collection order
     * @return Collection<T>
     */
    public function reverse(): Collection
    {
        return new self(array_reverse($this->toArray()));
    }

    // accessors
    /**
     * Do all of the items in the collection match a given criteria
     * @param  callable $iterator the criteria - should return true / false
     * @return bool
     */
    public function all(callable $iterator): bool
    {
        foreach ($this as $k => $v) {
            if (!call_user_func($iterator, $v, $k, $this)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Do any of the items in the collection match a given criteria
     * @param  callable $iterator the criteria - should return true / false
     * @return bool
     */
    public function any(callable $iterator): bool
    {
        foreach ($this as $k => $v) {
            if (call_user_func($iterator, $v, $k, $this)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Does the collection contain a given value
     * @param  mixed $needle the value to check for
     * @return bool
     */
    public function contains($needle): bool
    {
        foreach ($this as $k => $v) {
            if ($v === $needle) {
                return true;
            }
        }
        return false;
    }
    /**
     * Get the first element matching a given criteria (or null)
     * @param  callable $iterator the filter criteria
     * @return mixed
     */
    public function find(callable $iterator)
    {
        foreach ($this as $k => $v) {
            if (call_user_func($iterator, $v, $k, $this)) {
                return $v;
            }
        }
        return null;
    }
    /**
     * Get all the elements matching a given criteria (with the option to limit the number of results)
     * @param  callable(T, int|string, $this): bool $iterator the search criteria
     * @param  int|null $limit optional limit to the number of results (default to null - no limit)
     * @return Collection<T>
     */
    public function findAll(callable $iterator, int $limit = null): Collection
    {
        $res = [];
        foreach ($this as $k => $v) {
            if (call_user_func($iterator, $v, $k, $this)) {
                $res[] = $v;
            }
            if ((int)$limit > 0 && count($res) >= $limit) {
                break;
            }
        }
        return new self($res);
    }
    /**
     * Get the key corresponding to a value (or false)
     * @param  mixed  $needle the value to search for
     * @return mixed
     */
    public function indexOf($needle)
    {
        return array_search($needle, $this->toArray(), true);
    }
    /**
     * Get the last key corresponding to a value (or false)
     * @param  mixed  $needle the value to search for
     * @return mixed
     */
    public function lastIndexOf($needle)
    {
        $res = null;
        foreach ($this as $k => $v) {
            if ($v === $needle) {
                $res = $k;
            }
        }
        return $res;
    }
    /**
     * Get the number of elements in the collection
     * @return int
     */
    public function size(): int
    {
        return $this->count();
    }
    /**
     * Get the minimal item in the collection
     * @return mixed
     */
    public function min()
    {
        $min = null;
        $first = false;
        foreach ($this as $v) {
            if (!$first || $v < $min) {
                $min = $v;
                $first = true;
            }
        }
        return $min;
    }
    /**
     * Get the maximum item in the collection
     * @return mixed
     */
    public function max()
    {
        $max = null;
        $first = false;
        foreach ($this as $v) {
            if (!$first || $v > $max) {
                $max = $v;
                $first = true;
            }
        }
        return $max;
    }
    /**
     * Does the collection contain a given key
     * @param  string|int  $key the key to check
     * @return bool
     */
    public function has($key): bool
    {
        return $this->offsetExists($key);
    }
    /**
     * Reduce the collection to a single value
     * @param  callable $iterator the reducer (will recieve the carried value, the value, the key and the collection)
     * @param  mixed    $initial  the initial value
     * @return mixed the final value
     */
    public function reduce(callable $iterator, $initial = null)
    {
        foreach ($this as $k => $v) {
            $initial = $iterator($initial, $v, $k, $this);
        }
        return $initial;
    }
    /**
     * Reduce the collection to a single value, starting from the last element
     * @param  callable $iterator the reducer (will recieve the carried value, the value, the key and the collection)
     * @param  mixed    $initial  the initial value
     * @return mixed the final value
     */
    public function reduceRight(callable $iterator, $initial = null)
    {
        return $this->reverse()->reduce($iterator, $initial);
    }
}
