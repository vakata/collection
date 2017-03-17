<?php

namespace vakata\collection;

class Collection implements \Iterator, \ArrayAccess, \Serializable, \Countable
{
    protected $array = null;
    protected $stack = [];
    protected $iterator = null;

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
     * @return Collection
     */
    public static function range($low, $high, $step = 1) : Collection
    {
        return new static(static::rangeGenerator($low, $high, $step));
    }
    /**
     * A static alias of the __constructor
     * @param  mixed  $input  Anything iterable
     * @return Collection
     */
    public static function from($input) : Collection
    {
        return new static($input);
    }
    /**
     * Create an instance
     * @param  mixed  $input  Anything iterable
     */
    public function __construct($input = [])
    {
        if (is_object($input)) {
            if ($input instanceof \Iterator) {
                $this->array = $input;
                $this->iterator = $input;
            } else if ($input instanceof self) {
                $this->array = $input->toArray();
            } else {
                $input = get_object_vars($input);
            }
        }
        if (is_array($input)) {
            $this->array = new \ArrayObject($input);
            $this->iterator = $this->array->getIterator();
        }
    }
    public function __clone()
    {
        return new static($this->toArray());
    }
    public function __toString()
    {
        return implode(', ', $this->toArray());
    }
    public function serialize() {
        return serialize($this->toArray());
    }
    public function unserialize($array) {
        $this->array = new \ArrayObject(unserialize($array));
        $this->stack = [];
        $this->iterator = $this->array->getIterator();
    }

    /**
     * Applies all pending operations
     * @return $this
     */
    public function squash() : Collection
    {
        $this->array = new \ArrayObject(iterator_to_array($this));
        $this->stack = [];
        $this->iterator = $this->array->getIterator();
        return $this;
    }
    /**
     * Get an actual array from the collection
     * @return array
     */
    public function toArray() : array
    {
        $this->squash();
        return $this->array->getArrayCopy();
    }
    /**
     * Gets the first value in the collection or null if empty
     * @return mixed
     */
    public function value()
    {
        foreach ($this as $v) {
            return $v;
        }
        return null;
    }

    // iterator
    public function key()
    {
        return $this->iterator->key();
    }
    public function current()
    {
        $val = $this->iterator->current();
        $key = $this->iterator->key();
        foreach ($this->stack as $action) {
            if ($action[0] === 'map') {
                $val = call_user_func($action[1], $val, $key, $this);
            }
        }
        return $val;
    }
    public function rewind()
    {
        return $this->iterator->rewind();
    }
    public function next()
    {
        return $this->iterator->next();
    }
    public function valid()
    {
        while ($this->iterator->valid()) {
            $val = $this->iterator->current();
            $key = $this->iterator->key();
            $con = false;
            foreach ($this->stack as $action) {
                if ($action[0] === 'filter') {
                    if (!call_user_func($action[1], $val, $key, $this)) {
                        $con = true;
                        break;
                    }
                }
                if ($action[0] === 'map') {
                    $val = call_user_func($action[1], $val, $key, $this);
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

    // array access
    public function offsetGet($offset)
    {
        return $this->squash()->iterator->offsetGet($offset);
    }
    public function offsetExists($offset)
    {
        return $this->squash()->iterator->offsetExists($offset);
    }
    public function offsetUnset($offset)
    {
        return $this->squash()->iterator->offsetUnset($offset);
    }
    public function offsetSet($offset, $value)
    {
        return $this->squash()->iterator->offsetSet($offset, $value);
    }
    /**
     * Get the collection length
     * @return int
     */
    public function count()
    {
        $this->squash();
        return $this->array->count();
    }

    // mutators
    /**
     * Filter values from the collection based on a predicate. The callback will receive the value, key and collection
     * @param  callable $iterator the predicate
     * @return $this
     */
    public function filter(callable $iterator) : Collection
    {
        $this->stack[] = [ 'filter', $iterator ];
        return $this;
    }
    /**
     * Pass all values of the collection through a mutator callable, which will receive the value, key and collection
     * @param  callable $iterator the mutator
     * @return $this
     */
    public function map(callable $iterator) : Collection
    {
        $this->stack[] = [ 'map', $iterator ];
        return $this;
    }
    /**
     * Clone the current collection and return it.
     * @return Collection
     */
    public function clone() : Collection
    {
        return clone $this;
    }
    /**
     * Remove all falsy values from the collection (uses filter internally).
     * @return $this
     */
    public function compact() : Collection
    {
        return $this->filter(function ($v) {
            return !!$v;
        });
    }
    /**
     * Exclude all listed values from the collection (uses filter internally).
     * @param  iterable $values the values to exclude
     * @return $this
     */
    public function difference($values) : Collection
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
     * @param  iterable $source the values to add
     * @return Collection
     */
    public function extend($source) : Collection
    {
        if (!is_array($source)) {
            $source = iterator_to_array($source);
        }
        return new static(array_merge($this->toArray(), $source));
    }
    /**
     * Append more values to the collection
     * @param  iterable $source the values to add
     * @return Collection
     */
    public function merge($source) : Collection
    {
        return $this->extend($source);
    }
    /**
     * Perform a shallow flatten of the collection
     * @return Collection
     */
    public function flatten() : Collection
    {
        $rslt = [];
        $temp = $this->toArray();
        foreach ($temp as $v) {
            $rslt = array_merge($rslt, is_array($v) ? $v : [$v]);
        }
        return new static($rslt);
    }
    /**
     * Group by a key (if a callable is used - return the value to group by)
     * @param  string|callable $iterator the key to group by
     * @return Collection
     */
    public function groupBy($iterator) : Collection
    {
        $rslt = [];
        $temp = $this->toArray();
        foreach ($temp as $k => $v) {
            $rslt[is_string($iterator) ? (is_object($v) ? $v->{$iterator} : $v[$iterator]) : call_user_func($iterator, $v, $k)][] = $v;
        }
        return new static($rslt);
    }
    /**
     * Get the first X items from the collection
     * @param  int $count the number of items to include (defaults to 1)
     * @return Collection
     */
    public function first(int $count = 1) : Collection
    {
        $i = 0;
        $new = [];
        foreach ($this as $k => $v) {
            if (++$i > $count) {
                break;
            }
            $new[$k] = $v;
        }
        return new static($new);
    }
    /**
     * Get the first X items from the collection
     * @param  int $count the number of items to include (defaults to 1)
     * @return Collection
     */
    public function head(int $count = 1) : Collection
    {
        return $this->first($count);
    }
    /**
     * Get the last X items from the collection
     * @param  int $count the number of items to include (defaults to 1)
     * @return Collection
     */
    public function last(int $count = 1) : Collection
    {
        $new = $this->toArray();
        return new static(array_slice($new, $count * -1));
    }
    /**
     * Get the first X items from the collection
     * @param  int $count the number of items to include (defaults to 1)
     * @return Collection
     */
    public function tail(int $count = 1) : Collection
    {
        return $this->last($count);
    }
    /**
     * Get all but the last X items from the collection
     * @param  int $count the number of items to exclude (defaults to 1)
     * @return Collection
     */
    public function initial(int $count = 1) : Collection
    {
        $new = $this->toArray();
        return new static(array_slice($new, 0, $count * -1));
    }
    /**
     * Get all but the first X items from the collection
     * @param  int $count the number of items to exclude (defaults to 1)
     * @return Collection
     */
    public function rest(int $count = 1) : Collection
    {
        $new = $this->toArray();
        return new static(array_slice($new, $count));
    }
    /**
     * Execute a callable for each item in the collection (does not modify the collection)
     * @param  callable $iterator the callable to execute
     * @return $this
     */
    public function each(callable $iterator) : Collection
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
    public function invoke(callable $iterator) : Collection
    {
        return $this->each($iterator);
    }
    /**
     * Get all the collection keys
     * @return $this
     */
    public function keys() : Collection
    {
        return $this->map(function ($v, $k) { return $k; })->values();
    }
    /**
     * Pluck a value from each object (uses map internally)
     * @param  string|int $key the key to extract
     * @return $this
     */
    public function pluck($key) : Collection
    {
        return $this->map(function ($v) use ($key) {
            return is_object($v) ?
                (isset($v->{$key}) ? $v->{$key} : null) :
                (isset($v[$key]) ? $v[$key] : null);
        });
    }
    /**
     * Intersect the collection with another iterable (uses filter internally)
     * @param  interable $values the data to intersect with
     * @return $this
     */
    public function intersection($values) : Collection
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
    public function reject(callable $iterator) : Collection
    {
        return $this->filter(function ($v, $k, $array) use ($iterator) {
            return !call_user_func($iterator, $v, $k, $array);
        });
    }
    /**
     * Shuffle the values in the collection
     * @return Collection
     */
    public function shuffle() : Collection
    {
        $temp = $this->toArray();
        $keys = array_keys($temp);
        shuffle($keys);
        $rslt = [];
        foreach ($keys as $key) {
            $rslt[$key] = $temp[$key];
        }
        return new static($rslt);
    }
    /**
     * Sort the collection using a standard sorting function
     * @param  callable $iterator the sort function (must return -1, 0 or 1)
     * @return Collection
     */
    public function sortBy(callable $iterator) : Collection
    {
        $this->squash();
        $this->array->uasort($iterator);
        return $this;
    }
    /**
     * Inspect the whole collection (as an array) mid-chain
     * @param  callable $iterator the callable to execute
     * @return $this
     */
    public function tap(callable $iterator) : Collection
    {
        call_user_func($iterator, $this->toArray());
        return $this;
    }
    /**
     * Modify the whole collection (as an array) mid-chain
     * @param  callable $iterator the callable to execute
     * @return Collection
     */
    public function thru(callable $iterator) : Collection
    {
        $temp = $this->toArray();
        $rslt = call_user_func($iterator, $temp);
        return new static($rslt);
    }
    /**
     * Leave only unique items in the collection
     * @return Collection
     */
    public function unique() : Collection
    {
        $temp = $this->toArray();
        $rslt = [];
        foreach ($temp as $k => $v) {
            if (!in_array($v, $rslt, true)) {
                $rslt[$k] = $v;
            }
        }
        return new static($rslt);
    }
    /**
     * Get only the values of the collection
     * @return Collection
     */
    public function values() : Collection
    {
        return new static(array_values($this->toArray()));
    }
    /**
     * Filter items from the collection using key => value pairs
     * @param  array   $properties the key => value to check for in each item
     * @param  boolean $strict     should the comparison be strict
     * @return $this
     */
    public function where(array $properties, $strict = true) : Collection
    {
        return $this->filter(function ($v) use ($properties, $strict) {
            foreach ($properties as $key => $value) {
                $vv = is_object($v) ? (isset($v->{$key}) ? $v->{$key} : null) : (isset($v[$key]) ? $v[$key] : null);
                if (!$vv || ($strict && $vv !== $value) || (!$strict && $vv != $value)) {
                    return false;
                }
            }
            return true;
        });
    }
    /**
     * Exclude all listed values from the collection (uses filter internally).
     * @param  iterable $values the values to exclude
     * @return $this
     */
    public function without($values) : Collection
    {
        return $this->difference($values);
    }
    /**
     * Combine all the values from the collection with a key
     * @param  iterable $keys the keys to use
     * @return Collection
     */
    public function zip($keys) : Collection
    {
        if (!is_array($keys)) {
            $keys = iterator_to_array($keys);
        }
        return new static(array_combine($keys, $this->toArray()));
    }
    /**
     * Reverse the collection order
     * @return Collection
     */
    public function reverse() : Collection
    {
        return new static(array_reverse($this->toArray()));
    }

    // accessors
    /**
     * Do all of the items in the collection match a given criteria
     * @param  callable $iterator the criteria - should return true / false
     * @return bool
     */
    public function all(callable $iterator) : bool
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
    public function any(callable $iterator) : bool
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
    public function contains($needle) : bool
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
     * @param  callable $iterator the search criteria
     * @param  int|null $limit    optional limit to the number of results (default to null - no limit)
     * @return Collection
     */
    public function findAll(callable $iterator, int $limit = null) : Collection
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
        return new static($res);
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
    public function size() : int
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
    public function has($key) : bool
    {
        return $this->offsetExists($key);
    }
    /**
     * Reduce the collection to a single value
     * @param  callable $iterator the reducer
     * @param  mixed    $initial  the initial value
     * @return mixed the final value
     */
    public function reduce(callable $iterator, $initial = null)
    {
        return array_reduce($this->toArray(), $iterator, $initial);
    }
    /**
     * Reduce the collection to a single value, starting from the last element
     * @param  callable $iterator the reducer
     * @param  mixed    $initial  the initial value
     * @return mixed the final value
     */
    public function reduceRight(callable $iterator, $initial = null)
    {
        return array_reduce(array_reverse($this->toArray()), $iterator, $initial);
    }
}
