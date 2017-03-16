# vakata\collection\Collection  



## Implements:
Iterator, Traversable, ArrayAccess, Serializable, Countable



## Methods

| Name | Description |
|------|-------------|
|[__callStatic](#collection__callstatic)||
|[__clone](#collection__clone)||
|[__construct](#collection__construct)|Create an instance|
|[__debugInfo](#collection__debuginfo)||
|[__toString](#collection__tostring)||
|[all](#collectionall)|Do all of the items in the collection match a given criteria|
|[any](#collectionany)|Do any of the items in the collection match a given criteria|
|[clone](#collectionclone)|Clone the current collection and return it.|
|[compact](#collectioncompact)|Remove all falsy values from the collection (uses filter internally).|
|[contains](#collectioncontains)|Does the collection contain a given value|
|[count](#collectioncount)|Get the collection length|
|[current](#collectioncurrent)||
|[difference](#collectiondifference)|Exclude all listed values from the collection (uses filter internally).|
|[each](#collectioneach)|Execute a callable for each item in the collection (does not modify the collection)|
|[extend](#collectionextend)|Append more values to the collection|
|[filter](#collectionfilter)|Filter values from the collection based on a predicate. The callback will receive the value, key and collection|
|[find](#collectionfind)|Get the first element matching a given criteria (or null)|
|[findAll](#collectionfindall)|Get all the elements matching a given criteria (with the option to limit the number of results)|
|[first](#collectionfirst)|Get the first X items from the collection|
|[flatten](#collectionflatten)|Perform a shallow flatten of the collection|
|[from](#collectionfrom)|A static alias of the __constructor|
|[groupBy](#collectiongroupby)|Group by a key (if a callable is used - return the value to group by)|
|[has](#collectionhas)|Does the collection contain a given key|
|[head](#collectionhead)|Get the first X items from the collection|
|[indexOf](#collectionindexof)|Get the key corresponding to a value (or false)|
|[initial](#collectioninitial)|Get all but the last X items from the collection|
|[intersection](#collectionintersection)|Intersect the collection with another iterable (uses filter internally)|
|[invoke](#collectioninvoke)|Execute a callable for each item in the collection (does not modify the collection)|
|[key](#collectionkey)||
|[keys](#collectionkeys)|Get all the collection keys|
|[last](#collectionlast)|Get the last X items from the collection|
|[lastIndexOf](#collectionlastindexof)|Get the last key corresponding to a value (or false)|
|[map](#collectionmap)|Pass all values of the collection through a mutator callable, which will receive the value, key and collection|
|[max](#collectionmax)|Get the maximum item in the collection|
|[merge](#collectionmerge)|Append more values to the collection|
|[min](#collectionmin)|Get the minimal item in the collection|
|[next](#collectionnext)||
|[offsetExists](#collectionoffsetexists)||
|[offsetGet](#collectionoffsetget)||
|[offsetSet](#collectionoffsetset)||
|[offsetUnset](#collectionoffsetunset)||
|[pluck](#collectionpluck)|Pluck a value from each object (uses map internally)|
|[range](#collectionrange)|Create a collection based on a range generator|
|[reduce](#collectionreduce)|Reduce the collection to a single value|
|[reduceRight](#collectionreduceright)|Reduce the collection to a single value, starting from the last element|
|[reject](#collectionreject)|Reject values on a given predicate (opposite of filter)|
|[rest](#collectionrest)|Get all but the first X items from the collection|
|[reverse](#collectionreverse)|Reverse the collection order|
|[rewind](#collectionrewind)||
|[serialize](#collectionserialize)||
|[shuffle](#collectionshuffle)|Shuffle the values in the collection|
|[size](#collectionsize)|Get the number of elements in the collection|
|[sortBy](#collectionsortby)|Sort the collection using a standard sorting function|
|[squash](#collectionsquash)|Applies all pending operations|
|[tail](#collectiontail)|Get the first X items from the collection|
|[tap](#collectiontap)|Inspect the whole collection (as an array) mid-chain|
|[thru](#collectionthru)|Modify the whole collection (as an array) mid-chain|
|[toArray](#collectiontoarray)|Get an actual array from the collection|
|[unique](#collectionunique)|Leave only unique items in the collection|
|[unserialize](#collectionunserialize)||
|[valid](#collectionvalid)||
|[value](#collectionvalue)|Gets the first value in the collection or null if empty|
|[values](#collectionvalues)|Get only the values of the collection|
|[where](#collectionwhere)|Filter items from the collection using key => value pairs|
|[without](#collectionwithout)|Exclude all listed values from the collection (uses filter internally).|
|[zip](#collectionzip)|Combine all the values from the collection with a key|




### Collection::__callStatic  

**Description**

```php
public static __callStatic (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::__clone  

**Description**

```php
public __clone (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::__construct  

**Description**

```php
public __construct (mixed $input)
```

Create an instance 

 

**Parameters**

* `(mixed) $input`
: Anything iterable  

**Return Values**




### Collection::__debugInfo  

**Description**

```php
public __debugInfo (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::__toString  

**Description**

```php
public __toString (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::all  

**Description**

```php
public all (callable $iterator)
```

Do all of the items in the collection match a given criteria 

 

**Parameters**

* `(callable) $iterator`
: the criteria - should return true / false  

**Return Values**

`bool`





### Collection::any  

**Description**

```php
public any (callable $iterator)
```

Do any of the items in the collection match a given criteria 

 

**Parameters**

* `(callable) $iterator`
: the criteria - should return true / false  

**Return Values**

`bool`





### Collection::clone  

**Description**

```php
public clone (void)
```

Clone the current collection and return it. 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\Collection`





### Collection::compact  

**Description**

```php
public compact (void)
```

Remove all falsy values from the collection (uses filter internally). 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`$this`





### Collection::contains  

**Description**

```php
public contains (mixed $needle)
```

Does the collection contain a given value 

 

**Parameters**

* `(mixed) $needle`
: the value to check for  

**Return Values**

`bool`





### Collection::count  

**Description**

```php
public count (void)
```

Get the collection length 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`int`





### Collection::current  

**Description**

```php
public current (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::difference  

**Description**

```php
public difference (\iterable $values)
```

Exclude all listed values from the collection (uses filter internally). 

 

**Parameters**

* `(\iterable) $values`
: the values to exclude  

**Return Values**

`$this`





### Collection::each  

**Description**

```php
public each (callable $iterator)
```

Execute a callable for each item in the collection (does not modify the collection) 

 

**Parameters**

* `(callable) $iterator`
: the callable to execute  

**Return Values**

`$this`





### Collection::extend  

**Description**

```php
public extend (\iterable $values)
```

Append more values to the collection 

 

**Parameters**

* `(\iterable) $values`
: the values to add  

**Return Values**

`\Collection`





### Collection::filter  

**Description**

```php
public filter (callable $iterator)
```

Filter values from the collection based on a predicate. The callback will receive the value, key and collection 

 

**Parameters**

* `(callable) $iterator`
: the predicate  

**Return Values**

`$this`





### Collection::find  

**Description**

```php
public find (callable $iterator)
```

Get the first element matching a given criteria (or null) 

 

**Parameters**

* `(callable) $iterator`
: the filter criteria  

**Return Values**

`mixed`





### Collection::findAll  

**Description**

```php
public findAll (callable $iterator, int|null $limit)
```

Get all the elements matching a given criteria (with the option to limit the number of results) 

 

**Parameters**

* `(callable) $iterator`
: the search criteria  
* `(int|null) $limit`
: optional limit to the number of results (default to null - no limit)  

**Return Values**

`\Collection`





### Collection::first  

**Description**

```php
public first (int $count)
```

Get the first X items from the collection 

 

**Parameters**

* `(int) $count`
: the number of items to include (defaults to 1)  

**Return Values**

`\Collection`





### Collection::flatten  

**Description**

```php
public flatten (void)
```

Perform a shallow flatten of the collection 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\Collection`





### Collection::from  

**Description**

```php
public static from (mixed $input)
```

A static alias of the __constructor 

 

**Parameters**

* `(mixed) $input`
: Anything iterable  

**Return Values**

`\Collection`





### Collection::groupBy  

**Description**

```php
public groupBy (string|callable $iterator)
```

Group by a key (if a callable is used - return the value to group by) 

 

**Parameters**

* `(string|callable) $iterator`
: the key to group by  

**Return Values**

`\Collection`





### Collection::has  

**Description**

```php
public has (string|int $key)
```

Does the collection contain a given key 

 

**Parameters**

* `(string|int) $key`
: the key to check  

**Return Values**

`bool`





### Collection::head  

**Description**

```php
public head (int $count)
```

Get the first X items from the collection 

 

**Parameters**

* `(int) $count`
: the number of items to include (defaults to 1)  

**Return Values**

`\Collection`





### Collection::indexOf  

**Description**

```php
public indexOf (mixed $needle)
```

Get the key corresponding to a value (or false) 

 

**Parameters**

* `(mixed) $needle`
: the value to search for  

**Return Values**

`mixed`





### Collection::initial  

**Description**

```php
public initial (int $count)
```

Get all but the last X items from the collection 

 

**Parameters**

* `(int) $count`
: the number of items to exclude (defaults to 1)  

**Return Values**

`\Collection`





### Collection::intersection  

**Description**

```php
public intersection (\interable $values)
```

Intersect the collection with another iterable (uses filter internally) 

 

**Parameters**

* `(\interable) $values`
: the data to intersect with  

**Return Values**

`$this`





### Collection::invoke  

**Description**

```php
public invoke (callable $iterator)
```

Execute a callable for each item in the collection (does not modify the collection) 

 

**Parameters**

* `(callable) $iterator`
: the callable to execute  

**Return Values**

`$this`





### Collection::key  

**Description**

```php
public key (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::keys  

**Description**

```php
public keys (void)
```

Get all the collection keys 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`$this`





### Collection::last  

**Description**

```php
public last (int $count)
```

Get the last X items from the collection 

 

**Parameters**

* `(int) $count`
: the number of items to include (defaults to 1)  

**Return Values**

`\Collection`





### Collection::lastIndexOf  

**Description**

```php
public lastIndexOf (mixed $needle)
```

Get the last key corresponding to a value (or false) 

 

**Parameters**

* `(mixed) $needle`
: the value to search for  

**Return Values**

`mixed`





### Collection::map  

**Description**

```php
public map (callable $iterator)
```

Pass all values of the collection through a mutator callable, which will receive the value, key and collection 

 

**Parameters**

* `(callable) $iterator`
: the mutator  

**Return Values**

`$this`





### Collection::max  

**Description**

```php
public max (void)
```

Get the maximum item in the collection 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`mixed`





### Collection::merge  

**Description**

```php
public merge (\iterable $values)
```

Append more values to the collection 

 

**Parameters**

* `(\iterable) $values`
: the values to add  

**Return Values**

`\Collection`





### Collection::min  

**Description**

```php
public min (void)
```

Get the minimal item in the collection 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`mixed`





### Collection::next  

**Description**

```php
public next (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::offsetExists  

**Description**

```php
public offsetExists (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::offsetGet  

**Description**

```php
public offsetGet (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::offsetSet  

**Description**

```php
public offsetSet (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::offsetUnset  

**Description**

```php
public offsetUnset (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::pluck  

**Description**

```php
public pluck (string|int $key)
```

Pluck a value from each object (uses map internally) 

 

**Parameters**

* `(string|int) $key`
: the key to extract  

**Return Values**

`$this`





### Collection::range  

**Description**

```php
public static range (int|float $low, int|float $high, int|float $step)
```

Create a collection based on a range generator 

 

**Parameters**

* `(int|float) $low`
: start value  
* `(int|float) $high`
: end value  
* `(int|float) $step`
: increment  

**Return Values**

`\Collection`





### Collection::reduce  

**Description**

```php
public reduce (callable $iterator, mixed $initial)
```

Reduce the collection to a single value 

 

**Parameters**

* `(callable) $iterator`
: the reducer  
* `(mixed) $initial`
: the initial value  

**Return Values**

`mixed`

> the final value  




### Collection::reduceRight  

**Description**

```php
public reduceRight (callable $iterator, mixed $initial)
```

Reduce the collection to a single value, starting from the last element 

 

**Parameters**

* `(callable) $iterator`
: the reducer  
* `(mixed) $initial`
: the initial value  

**Return Values**

`mixed`

> the final value  




### Collection::reject  

**Description**

```php
public reject (callable $iterator)
```

Reject values on a given predicate (opposite of filter) 

 

**Parameters**

* `(callable) $iterator`
: the predicate  

**Return Values**

`$this`





### Collection::rest  

**Description**

```php
public rest (int $count)
```

Get all but the first X items from the collection 

 

**Parameters**

* `(int) $count`
: the number of items to exclude (defaults to 1)  

**Return Values**

`\Collection`





### Collection::reverse  

**Description**

```php
public reverse (void)
```

Reverse the collection order 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\Collection`





### Collection::rewind  

**Description**

```php
public rewind (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::serialize  

**Description**

```php
public serialize (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::shuffle  

**Description**

```php
public shuffle (void)
```

Shuffle the values in the collection 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\Collection`





### Collection::size  

**Description**

```php
public size (void)
```

Get the number of elements in the collection 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`int`





### Collection::sortBy  

**Description**

```php
public sortBy (callable $iterator)
```

Sort the collection using a standard sorting function 

 

**Parameters**

* `(callable) $iterator`
: the sort function (must return -1, 0 or 1)  

**Return Values**

`\Collection`





### Collection::squash  

**Description**

```php
public squash (void)
```

Applies all pending operations 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`$this`





### Collection::tail  

**Description**

```php
public tail (int $count)
```

Get the first X items from the collection 

 

**Parameters**

* `(int) $count`
: the number of items to include (defaults to 1)  

**Return Values**

`\Collection`





### Collection::tap  

**Description**

```php
public tap (callable $iterator)
```

Inspect the whole collection (as an array) mid-chain 

 

**Parameters**

* `(callable) $iterator`
: the callable to execute  

**Return Values**

`$this`





### Collection::thru  

**Description**

```php
public thru (callable $iterator)
```

Modify the whole collection (as an array) mid-chain 

 

**Parameters**

* `(callable) $iterator`
: the callable to execute  

**Return Values**

`\Collection`





### Collection::toArray  

**Description**

```php
public toArray (void)
```

Get an actual array from the collection 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`array`





### Collection::unique  

**Description**

```php
public unique (void)
```

Leave only unique items in the collection 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\Collection`





### Collection::unserialize  

**Description**

```php
public unserialize (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::valid  

**Description**

```php
public valid (void)
```

 

 

**Parameters**

`This function has no parameters.`

**Return Values**




### Collection::value  

**Description**

```php
public value (void)
```

Gets the first value in the collection or null if empty 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`mixed`





### Collection::values  

**Description**

```php
public values (void)
```

Get only the values of the collection 

 

**Parameters**

`This function has no parameters.`

**Return Values**

`\Collection`





### Collection::where  

**Description**

```php
public where (array $properties, boolean $strict)
```

Filter items from the collection using key => value pairs 

 

**Parameters**

* `(array) $properties`
: the key => value to check for in each item  
* `(boolean) $strict`
: should the comparison be strict  

**Return Values**

`$this`





### Collection::without  

**Description**

```php
public without (\iterable $values)
```

Exclude all listed values from the collection (uses filter internally). 

 

**Parameters**

* `(\iterable) $values`
: the values to exclude  

**Return Values**

`$this`





### Collection::zip  

**Description**

```php
public zip (\iterable $keys)
```

Combine all the values from the collection with a key 

 

**Parameters**

* `(\iterable) $keys`
: the keys to use  

**Return Values**

`\Collection`




