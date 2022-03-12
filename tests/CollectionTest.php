<?php

namespace vakata\collection\test;

use vakata\collection\Collection;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    protected function getDummy()
    {
        $dummy = new \stdClass();
        $dummy->name = 'dummy';
        $dummy->foo = 'bar';
        $dummy->baz = 'qux';

        return $dummy;
    }

    /**
     * @return object
     */
    protected function getDummy2()
    {
        $dummy = $this->getDummy();
        $dummy->false = false;
        $dummy->null = null;
        $dummy->zero = 0;

        return $dummy;
    }

    protected function getDummy3()
    {
        $dummy = [
            'Angela' => [
                'position' => 'dean',
                'sex'      => 'female',
                'kids'     => 3
            ],
            'Bob'    => [
                'position' => 'janitor',
                'sex'      => 'male',
                'kids'     => 0
            ],
            'Mark'   => [
                'position' => 'teacher',
                'sex'      => 'male',
                'tenured'  => true,
                'kids'     => 2
            ],
            'Wendy'  => [
                'position' => 'teacher',
                'sex'      => 'female',
                'tenured'  => 1,
                'kids'     => 1
            ],
        ];

        return $dummy;
    }

    /**
     * @return array
     */
    public function getTestRangeData()
    {
        $out = [];
        // case #0
        $out[] = [0, 4, 1, [0, 1, 2, 3, 4]];
        // case #1
        $out[] = [1, 5, 1, [1, 2, 3, 4, 5]];
        // case #2
        $out[] = [0, 20, 5, [0, 5, 10, 15, 20]];
        // case #3
        $out[] = [0, 0, 1, [0]];

        return $out;
    }

    /**
     * @dataProvider getTestRangeData
     */
    public function testRange($start, $stop, $step, $expected, $exception = null)
    {
        $exception && $this->setExpectedException($exception);

        $this->assertEquals(
            $expected,
            Collection::range($start, $stop, $step)->toArray()
        );
    }

    //public function testValue()
    //{
    //    $value = Collection::from($this->getDummy())->value();
    //    $this->assertEquals((array)$this->getDummy(), $value);
    //}

    public function testInvoke()
    {
        $buffer = '';
        Collection::from($this->getDummy())
            ->invoke(
                function ($value, $key) use (&$buffer) {
                    $buffer .= sprintf('%s:%s|', $key, $value);
                }
            );
        $this->assertSame('name:dummy|foo:bar|baz:qux|', $buffer);
    }

    public function testMap()
    {
        $value = Collection::from($this->getDummy())
            ->map(
                function ($value, $key) use (&$buffer) {
                    return sprintf('%s:%s', $key, $value);
                }
            )->toArray();

        $this->assertSame(
            ['name' => 'name:dummy', 'foo' => 'foo:bar', 'baz' => 'baz:qux'],
            $value
        );
    }

    public function testReduce()
    {
        $value = Collection::from($this->getDummy())
            ->reduce(
                function ($accu, $value) {
                    $accu .= $value . ' ';

                    return $accu;
                },
                ''
            );

        $this->assertSame('dummy bar qux ', $value);
    }

    public function testReduceRight()
    {
        $value = Collection::from($this->getDummy())
            ->reduceRight(
                function ($accumulator, $value) {
                    $accumulator .= $value . ' ';

                    return $accumulator;
                },
                ''
            );

        $this->assertSame('qux bar dummy ', $value);
    }

    public function testIndexOf()
    {
        $value = Collection::from($this->getDummy())
            ->indexOf('dummy');

        $this->assertSame('name', $value);

        $value = Collection::from($this->getDummy2())
            ->indexOf(false);

        $this->assertSame('false', $value);
    }

    public function testLastIndexOf()
    {
        $value = Collection::from([1, 2, 3])
            ->lastIndexOf(2);

        $this->assertSame(1, $value);

        $value = Collection::from([1, 2, 3, 4, 2])
            ->lastIndexOf(2);

        $this->assertSame(4, $value);

        $object = new \stdClass;
        $value = Collection::from([clone $object, clone $object, $object])
            ->lastIndexOf($object);

        $this->assertSame(2, $value);
    }

    public function testMin()
    {
        $value = Collection::from([1, 5, 1, 100, 8, 3])
            ->min();

        $this->assertSame(1, $value);
    }

    public function testMax()
    {
        $value = Collection::from([1, 5, 1, 100, 8, 3])
            ->max();

        $this->assertSame(100, $value);
    }

    public function testpluck()
    {
        $value = Collection::from([$this->getDummy(), $this->getDummy(), $this->getDummy()])
            ->pluck('foo')
            ->toArray();

        $this->assertSame(['bar', 'bar', 'bar'], $value);

        $value = Collection::from($this->getDummy3())
            ->pluck('position')
            ->toArray();

        $this->assertSame([
            'Angela' => 'dean',
            'Bob' => 'janitor',
            'Mark' => 'teacher',
            'Wendy' => 'teacher',
        ], $value);
    }

    public function testContains()
    {
        $this->assertTrue(Collection::from(['bar'])->contains('bar'));
        $this->assertFalse(Collection::from(['bar'])->contains('baz'));
    }

    public function testFind()
    {
        $iterator = function ($needle) {
            return function ($value) use ($needle) {
                return $value === $needle;
            };
        };
        $this->assertSame('bar', Collection::from($this->getDummy())->find($iterator('bar')));
        $this->assertNull(Collection::from($this->getDummy())->find($iterator('foo')));
    }

    public function testFilter()
    {
        $value = Collection::from($this->getDummy())
            ->filter(
                function ($value) {
                    return 3 < strlen($value);
                }
            )
            ->toArray();

        $this->assertSame(['name' => 'dummy'], $value);
    }

    public function testReject()
    {
        $value = Collection::from($this->getDummy())
            ->reject(
                function ($value) {
                    return 3 < strlen($value);
                }
            )
            ->toArray();

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $value);
    }

    public function testAny()
    {
        $value = Collection::from($this->getDummy())
            ->any(
                function ($value) {
                    return 3 < strlen($value);
                }
            );

        $this->assertSame(true, $value);

        $value = Collection::from($this->getDummy())
            ->any(
                function ($value) {
                    return strlen($value) < 2;
                }
            );

        $this->assertSame(false, $value);
    }

    public function testAll()
    {
        $value = Collection::from($this->getDummy())
            ->all(
                function ($value) {
                    return 3 <= strlen($value);
                }
            );

        $this->assertSame(true, $value);

        $value = Collection::from($this->getDummy())
            ->all(
                function ($value) {
                    return 3 < strlen($value);
                }
            );

        $this->assertSame(false, $value);
    }

    public function testSize()
    {
        $value = Collection::from($this->getDummy())
            ->size();

        $this->assertSame(3, $value);
    }

    public function testHead()
    {
        $value = Collection::from([1,2,3,4])
            ->head(2)->toArray();

        $this->assertSame([1, 2], $value);
    }

    public function testTail()
    {
        $value = Collection::from([1,2,3,4])
            ->tail(1)->value();

        $this->assertSame(4, $value);
    }

    public function testInitial()
    {
        $value = Collection::from([1,2,3,4])
            ->initial(1)->toArray();

        $this->assertSame([1,2,3], $value);
    }

    public function testLast()
    {
        $value = Collection::from([1,2,3])
            ->last(2)->toArray();

        $this->assertSame([2, 3], $value);
    }

    public function testCompact()
    {
        $value = Collection::from($this->getDummy2())
            ->compact()
            ->toArray();

        $this->assertSame(['name' => 'dummy', 'foo' => 'bar', 'baz' => 'qux'], $value);
    }

    public function testWithout()
    {
        $value = Collection::from($this->getDummy())
            ->without(['dummy'])
            ->toArray();

        $this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $value);
    }

    public function testMerge()
    {
        $value = Collection::from($this->getDummy())
            ->merge(new Collection($this->getDummy2()))
            ->toArray();

        $this->assertSame(
            [
                'name'  => 'dummy',
                'foo'   => 'bar',
                'baz'   => 'qux',
                'false' => false,
                'null'  => null,
                'zero'  => 0,
            ],
            $value
        );
    }

    public function testDifference()
    {
        $one = ['yellow', 'pink', 'red', 'white', 'blue'];
        $two = ['red', 'white', 'blue'];

        $value = Collection::from($one)
            ->difference($two)
            ->toArray();

        $this->assertSame(array_values($value), [
            'yellow',
            'pink',
        ]);

        $one = [
            'name'  => 'John',
            'food'  => 'bacon',
            'sport' => 'tennis',
            'color' => 'red',
        ];

        $two = [
            'name'  => 'John',
            'color' => 'gray',
            'food'  => 'tofu',
            'sport' => 'tennis',
        ];

        $value = Collection::from($one)
            ->difference($two)
            ->toArray();

        // In this scenario, preserving keys is important.
        $this->assertSame($value, [
            'food'  => 'bacon',
            'color' => 'red',
        ]);

        // No difference, with a Traversable
        $values = new \ArrayObject([
            'foo' => 'bar',
            'fizz' => 'bizz',
        ]);

        $value = Collection::from($values)
            ->difference($values)
            ->toArray();

        $this->assertEmpty($value);
    }

    public function testIntersection()
    {
        $one = ['yellow', 'pink', 'white'];
        $two = ['red', 'white', 'blue'];

        $value = Collection::from($one)
            ->intersection($two)
            ->toArray();

        // Intersection will preserve keys, but we do not need to verify that.
        $this->assertSame(array_values($value), [
            'white',
        ]);

        // Run the same test, but with an array instead of a collection.
        $value = Collection::from($one)
            ->intersection(new Collection($two))
            ->toArray();

        $this->assertSame(array_values($value), [
            'white',
        ]);

        $one = [
            'name'  => 'John',
            'food'  => 'bacon',
            'sport' => 'tennis',
            'color' => 'red',
        ];

        $two = [
            'name'  => 'John',
            'color' => 'gray',
            'food'  => 'tofu',
            'sport' => 'tennis',
        ];

        $value = Collection::from($one)
            ->intersection($two)
            ->toArray();

        // In this scenario, preserving keys is important.
        $this->assertSame($value, [
            'name'  => 'John',
            'sport' => 'tennis',
        ]);
    }

    public function testValues()
    {
        $value = Collection::from($this->getDummy())
            ->values()
            ->toArray();

        $this->assertSame(
            [
                'dummy',
                'bar',
                'qux',
            ],
            $value
        );
    }

    public function testKeys()
    {
        $value = Collection::from($this->getDummy())
            ->keys()
            ->toArray();

        $this->assertSame(
            [
                'name',
                'foo',
                'baz',
            ],
            $value
        );
    }

    public function testHas()
    {
        $collection = Collection::from($this->getDummy());

        $this->assertTrue($collection->has('name'));
        $this->assertTrue($collection->has('foo'));
        $this->assertTrue($collection->has('baz'));

        $this->assertFalse($collection->has('nope'));
        $this->assertFalse($collection->has('missing'));
    }

    public function testClone()
    {
        $original = $this->getDummy();
        $cloned = Collection::from($original)
            ->clone()
            ->without(['dummy'])
            ->value();

        $this->assertNotEquals(
            Collection::from($original)->value(),
            $cloned
        );
    }

    public function testZip()
    {
        $value = Collection::from($this->getDummy())
            ->zip(['a', 1, '42'])
            ->toArray();

        $this->assertSame(
            [
                'a'  => 'dummy',
                1    => 'bar',
                '42' => 'qux',
            ],
            $value
        );
    }

    public function testGroupBy()
    {
        $value = Collection::from($this->getDummy())
            ->groupBy(function ($v) { return strlen($v); })
            ->toArray();

        $this->assertSame(
            [
                5 => ['dummy'],
                3 => ['bar', 'qux'],
            ],
            $value
        );
    }

    public function testSortBy()
    {
        $value = Collection::from(['bar', 'dummy', 'qux'])
            ->sortBy(function ($a, $b) { return strlen($a) <=> strlen($b); }, false)
            ->toArray();

        $this->assertSame(
            [
                0 => 'bar',
                2 => 'qux',
                1 => 'dummy',
            ],
            $value
        );
    }

    /**
     * @return array
     */
    public function getTestFlattenData()
    {
        $out = [];
        // case #0
        $out[] = [
            [1, 2, [3, 4]],
            [1, 2, 3, 4],
        ];

        return $out;
    }

    /**
     * @dataProvider getTestFlattenData
     */
    public function testFlatten($input, $expected)
    {
        $value = Collection::from($input)
            ->flatten()
            ->toArray();

        $this->assertSame($expected, $value);
    }

    public function testTap()
    {
        $dummy = $this->getDummy();

        $mock = $this->getMockBuilder('stdClass')->addMethods(['test'])->getMock(); //, ['test']);
        $mock->expects($this->once())->method('test')->with((array)$dummy);

        Collection::from($dummy)->tap([$mock, 'test']);
    }

    /**
     * @return array
     */
    public function getTestUniqData()
    {
        $out = [];
        // case #0
        $out[] = [
            [1, 2, 3, 4, 4, 3],
            [1, 2, 3, 4],
        ];
        // case #1
        $obj1 = new \StdClass;
        $obj2 = new \StdClass;
        $obj3 = $obj1;
        $out[] = [
            [$obj1, $obj1, $obj2, $obj3],
            [$obj1, $obj2],
        ];
        // case #2
        $out[] = [
            [true, false, 1, 0, 0.0, 0.00001],
            [true, false, 1, 0, 0.0, 0.00001],
        ];

        return $out;
    }

    /**
     * @dataProvider getTestUniqData
     */
    public function testUniq($input, $expected)
    {
        $value = Collection::from($input)
            ->unique()
            ->toArray();

        $this->assertEquals(array_values($expected), array_values($value));
    }

    public function testExtend()
    {
        $collection = Collection::from($this->getDummy())
            ->extend([
                'name' => 'extended',
            ]);

        $this->assertSame([
            'name' => 'extended',
            'foo'  => 'bar',
            'baz'  => 'qux',
        ], $collection->toArray());
    }

    public function testWhere()
    {
        $found = Collection::from($this->getDummy3())
            ->where([
                'sex' => 'female',
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Angela', 'Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'position' => 'teacher',
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Mark', 'Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'position' => 'teacher',
                'tenured'  => true,
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Mark'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'position' => 'teacher',
                'tenured'  => true,
            ], $strict = false)
            ->keys()
            ->toArray();

        $this->assertSame(['Mark', 'Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'sex'      => 'female',
                'position' => 'teacher',
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'sex'      => 'male',
                'position' => 'dean',
            ])
            ->keys()
            ->toArray();

        $this->assertSame([], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'position' => ['teacher', 'dean' ],
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Angela','Mark','Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'kids' => ['beg' => 1, 'end' => 2],
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Mark','Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'kids' => ['gte' => 1, 'lte' => 2],
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Mark','Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'kids' => ['lte' => 2],
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Bob','Mark','Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'kids' => ['lt' => 2],
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Bob','Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->where([
                'kids' => [1,3],
            ])
            ->keys()
            ->toArray();

        $this->assertSame(['Angela','Wendy'], $found);
    }

    public function testWhereAll()
    {
        $found = Collection::from($this->getDummy3())
            ->whereAll([
                ['sex' => 'female'],
                ['kids' => 3]
            ])
            ->keys()
            ->toArray();
        $this->assertSame(['Angela'], $found);

        $found = Collection::from($this->getDummy3())
            ->whereAll([
                ['sex' => 'female'],
                ['kids' => ['not' => 2]]
            ])
            ->keys()
            ->toArray();
        $this->assertSame(['Angela','Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->whereAll([
                ['sex' => 'female'],
                ['sex' => 'male']
            ])
            ->keys()
            ->toArray();
        $this->assertSame([], $found);
    }

    public function testWhereAny()
    {
        $found = Collection::from($this->getDummy3())
            ->whereAny([
                ['sex' => 'female'],
                ['kids' => 3]
            ])
            ->keys()
            ->toArray();
        $this->assertSame(['Angela','Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->whereAny([
                ['sex' => 'female'],
                ['kids' => ['not' => 2]]
            ])
            ->keys()
            ->toArray();
        $this->assertSame(['Angela','Bob','Wendy'], $found);

        $found = Collection::from($this->getDummy3())
            ->whereAny([
                ['sex' => 'female'],
                ['sex' => 'male']
            ])
            ->keys()
            ->toArray();
        $this->assertSame(['Angela','Bob','Mark','Wendy'], $found);
    }

    public function testShuffle()
    {
        $original = $this->getDummy3();
        $values = Collection::from($original)
            ->shuffle()
            ->toArray();

        // Not necessarily the same, but contains the same values.
        // We cannot check the sorting of the keys because it is entirely
        // possible that keys were not randomized at all!
        $this->assertEquals($original, $values);
    }
    public function testCollection()
    {
        $ar = [1,2,3];
        $c1 = Collection::from($ar);
        $c2 = Collection::from($c1);
        $this->assertSame($ar, $c2->toArray());
    }
    public function testString()
    {
        $this->assertSame('1, 2', (string)Collection::from([1, 2]));
    }
    public function testSerialize()
    {
        $c1 = Collection::from([1,2,3]);
        $c2 = unserialize(serialize($c1));
        $this->assertSame($c1->toArray(), $c2->toArray());
    }
    public function testNullValue()
    {
        $this->assertNull(Collection::from([])->value());
        $this->assertSame(1, Collection::from([1])->value());
    }
    public function testOffsets()
    {
        $c1 = Collection::from([1,2,3]);
        $this->assertSame(2, $c1[1]);
        $c1[2] = 4;
        $this->assertSame(4, $c1[2]);
        $c1['asdf'] = 'asdf';
        $this->assertSame([1,2,4,'asdf' => 'asdf'], $c1->toArray());
        unset($c1['asdf']);
        $this->assertSame([1,2,4], $c1->toArray());
    }
    public function testRest()
    {
        $this->assertSame([3,4,5], Collection::from([1,2,3,4,5])->rest(2)->toArray());
    }
    public function testThru()
    {
        $this->assertSame([1,2,3], Collection::from([1,2,3,4,5])->thru(function ($c) { return [1,2,3]; })->toArray());
    }
    public function testIteratorKeys()
    {
        $this->assertSame([1 => 1,2,3], Collection::from([1,2,3])->zip(new \ArrayObject([1,2,3]))->toArray());
    }
    public function testReverse()
    {
        $this->assertSame([1,2,3], Collection::from([3,2,1])->reverse()->toArray());
    }
    public function testFindAll()
    {
        $c1 = Collection::from([1,2,3,4,5,6,7,8]);
        $this->assertSame([2,4,6,8], $c1->findAll(function ($v) { return $v % 2 === 0; })->toArray());
        $this->assertSame([2,4], $c1->findAll(function ($v) { return $v % 2 === 0; }, 2)->toArray());
    }
    public function testMapKey()
    {
        $c1 = Collection::from([ [ "id" => 1, "name" => "A" ], [ "id" => 2, "name" => "B" ], [ "id" => 3, "name" => "C" ] ]);
        $this->assertSame(
            [1 => "A", 2 => "B", 3 => "C"],
            $c1->mapKey(function ($v) { return $v["id"]; })->pluck("name")->toArray()
        );
    }
}
