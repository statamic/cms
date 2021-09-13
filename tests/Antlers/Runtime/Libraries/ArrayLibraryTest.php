<?php

namespace Tests\Antlers\Runtime\Libraries;

use ArrayObject;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Tests\Antlers\ParserTestCase;

class ArrayLibraryTest extends ParserTestCase
{
    protected $arrData = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->arrData = [
            'simple' => ['one', 'two', 'three'],
            'simple_two' => ['one', 'two', 'three', 'four', 'five'],
        ];
    }

    public function test_arr_range()
    {
        $this->assertSame(range('c', 'a'), $this->evaluateRaw('arr.range("c", "a")'));
        $this->assertSame(range('z', 'a', 2), $this->evaluateRaw('arr.range("z", "a", 2)'));
        $this->assertSame(range(1, 100, 5), $this->evaluateRaw('arr.range(1, 100, 5)'));
    }

    public function test_array_count()
    {
        $this->assertSame(3, $this->evaluateRaw('arr.count(simple)', $this->arrData));
        $this->assertSame(5, $this->evaluateRaw('arr.count(simple_two)', $this->arrData));
    }

    public function test_array_explode()
    {
        $this->assertSame(['one', 'two', 'three'], $this->evaluateRaw('arr.explode(",", "one,two,three")', $this->arrData));
    }

    public function test_array_get()
    {
        $this->assertEquals(['price' => 100], $this->evaluateRaw('arr.get(array, "products.desk")', [
            'array' => ['products.desk' => ['price' => 100]],
        ]));
        $this->assertEquals(['price' => 100], $this->evaluateRaw('arr.get(array, "products.desk")', [
            'array' => ['products' => ['desk' => ['price' => 100]]],
        ]));

        $array = ['foo' => null, 'bar' => ['baz' => null]];
        $this->assertNull($this->evaluateRaw('array.get(array, "foo", "default")', ['array' => $array]));
        $this->assertNull($this->evaluateRaw('array.get(array, "bar.baz", "default")', ['array' => $array]));

        $array = ['products' => ['desk' => ['price' => 100]]];
        $arrayAccessObject = new ArrayObject($array);
        $this->assertEquals(['price' => 100], $this->evaluateRaw('array.get(array, "products.desk")', [
            'array' => $arrayAccessObject,
        ]));

        $arrayAccessChild = new ArrayObject(['products' => ['desk' => ['price' => 100]]]);
        $array = ['child' => $arrayAccessChild];

        $this->assertEquals(['price' => 100], $this->evaluateRaw('array.get(array, "child.products.desk")', [
            'array' => $array,
        ]));
    }

    public function test_array_has()
    {
        $array = ['products.desk' => ['price' => 100]];
        $this->assertTrue($this->evaluateRaw('arr.has(array, "products.desk")', ['array' => $array]));

        $array = ['products' => ['desk' => ['price' => 100]]];
        $this->assertTrue($this->evaluateRaw('arr.has(array, "products.desk")', ['array' => $array]));
        $this->assertTrue($this->evaluateRaw('arr.has(array, "products.desk.price")', ['array' => $array]));
        $this->assertFalse($this->evaluateRaw('arr.has(array, "products.foo")', ['array' => $array]));
        $this->assertFalse($this->evaluateRaw('arr.has(array, "products.desk.foo")', ['array' => $array]));

        $array = new ArrayObject(['foo' => 10, 'bar' => new ArrayObject(['baz' => 10])]);
        $this->assertTrue($this->evaluateRaw('arr.has(array, "foo")', ['array' => $array]));
        $this->assertTrue($this->evaluateRaw('arr.has(array, "bar")', ['array' => $array]));
        $this->assertTrue($this->evaluateRaw('arr.has(array, "bar.baz")', ['array' => $array]));
        $this->assertFalse($this->evaluateRaw('arr.has(array, "xxx")', ['array' => $array]));
        $this->assertFalse($this->evaluateRaw('arr.has(array, "xxx.yyy")', ['array' => $array]));
        $this->assertFalse($this->evaluateRaw('arr.has(array, "foo.xxx")', ['array' => $array]));
        $this->assertFalse($this->evaluateRaw('arr.has(array, "bar.xxx")', ['array' => $array]));
    }

    public function test_array_has_any()
    {
        $array = ['foo' => ['bar' => null, 'baz' => '']];

        $this->assertTrue($this->evaluateRaw('arr.hasAny(array, "foo.bar")', ['array' => $array]));
        $this->assertTrue($this->evaluateRaw('arr.hasAny(array, "foo.baz")', ['array' => $array]));
        $this->assertFalse($this->evaluateRaw('arr.hasAny(array, "foo.bax")', ['array' => $array]));
        $this->assertTrue($this->evaluateRaw('arr.hasAny(array, array_two)', ['array' => $array, 'array_two' => [
            'foo.bax', 'foo.baz',
        ]]));
        $this->assertTrue($this->evaluateRaw('arr.hasAny(array, arr.explode(",", "foo.bax,foo.baz"))', ['array' => $array]));
    }

    public function test_array_assoc()
    {
        $this->assertTrue($this->evaluateRaw('arr.assoc(array)', ['array' => ['a' => 'a', 0 => 'b']]));
        $this->assertTrue($this->evaluateRaw('arr.assoc(array)', ['array' => ['one' => 'a', 'two' => 'b']]));
        $this->assertFalse($this->evaluateRaw('arr.assoc(array)', ['array' => [1 => 'a', 0 => 'b']]));
        $this->assertFalse($this->evaluateRaw('arr.assoc(array)', ['array' => [1 => 'a', 2 => 'b']]));
        $this->assertFalse($this->evaluateRaw('arr.assoc(array)', ['array' => [0 => 'a', 1 => 'b']]));
        $this->assertFalse($this->evaluateRaw('arr.assoc(array)', ['array' => ['a', 'b']]));
    }

    public function test_array_is_assoc()
    {
        $this->assertTrue($this->evaluateRaw('arr.isAssoc(array)', ['array' => ['a' => 'a', 0 => 'b']]));
        $this->assertTrue($this->evaluateRaw('arr.isAssoc(array)', ['array' => [1 => 'a', 0 => 'b']]));
        $this->assertTrue($this->evaluateRaw('arr.isAssoc(array)', ['array' => [1 => 'a', 2 => 'b']]));
        $this->assertFalse($this->evaluateRaw('arr.isAssoc(array)', ['array' => [0 => 'a', 1 => 'b']]));
        $this->assertFalse($this->evaluateRaw('arr.isAssoc(array)', ['array' => ['a', 'b']]));
    }

    public function test_array_dot()
    {
        $this->assertEquals(['foo.bar' => 'baz'], $this->evaluateRaw('arr.dot(array)', [
            'array' => ['foo' => ['bar' => 'baz']],
        ]));
    }

    public function test_array_add()
    {
        $this->assertEquals(['name' => 'Desk', 'price' => 100], $this->evaluateRaw(
            'arr.add(test, "price", 100)', ['test' => ['name' => 'Desk']]
        ));
    }

    public function test_array_collapse()
    {
        $this->assertEquals(['foo', 'bar', 'baz'], $this->evaluateRaw(
            'arr.collapse(test)', ['test' => [['foo', 'bar'], ['baz']]]
        ));

        $this->assertEquals([1, 2, 3, 'foo', 'bar', 'baz', 'boom'], $this->evaluateRaw(
            'arr.collapse(test)', ['test' => [[1], [2], [3], ['foo', 'bar'], collect(['baz', 'boom'])]]
        ));
    }

    public function test_array_cross_join()
    {
        $this->assertSame([[1, 'a'], [1, 'b'], [1, 'c']], $this->evaluateRaw(
            'arr.crossJoin(array_one, array_two)', [
                'array_one' => [1],
                'array_two' => ['a', 'b', 'c'],
            ]
        ));
    }

    public function test_array_divide()
    {
        [$keys, $values] = $this->evaluateRaw('arr.divide(array)', [
            'array' => ['name' => 'Desk'],
        ]);

        $this->assertEquals(['name'], $keys);
        $this->assertEquals(['Desk'], $values);
    }

    public function test_array_except()
    {
        $array = ['name' => 'taylor', 'framework' => ['language' => 'PHP', 'name' => 'Laravel']];

        $this->assertEquals(['name' => 'taylor'], $this->evaluateRaw('arr.except(array, "framework")', ['array' => $array]));
        $this->assertEquals(['name' => 'taylor', 'framework' => ['name' => 'Laravel']],
            $this->evaluateRaw('arr.except(array, "framework.language")', ['array' => $array])
        );
        $this->assertEquals(['framework' => ['language' => 'PHP']],
            $this->evaluateRaw('arr.except(array, arr.explode(",", "name,framework.name"))', ['array' => $array])
        );
    }

    public function test_array_exists()
    {
        $this->assertTrue($this->evaluateRaw('arr.exists(array, 0)', ['array' => [1]]));
        $this->assertTrue($this->evaluateRaw('arr.exists(array, 0)', ['array' => [null]]));
        $this->assertTrue($this->evaluateRaw('arr.exists(array, "a")', ['array' => ['a' => 1]]));
        $this->assertTrue($this->evaluateRaw('arr.exists(array, "a")', ['array' => ['a' => null]]));
        $this->assertTrue($this->evaluateRaw('arr.exists(array, "a")', ['array' => new Collection(['a' => null])]));

        $this->assertFalse($this->evaluateRaw('arr.exists(array, 1)', ['array' => [1]]));
        $this->assertFalse($this->evaluateRaw('arr.exists(array, 1)', ['array' => [null]]));
        $this->assertFalse($this->evaluateRaw('arr.exists(array, 0)', ['array' => ['a' => 1]]));
        $this->assertFalse($this->evaluateRaw('arr.exists(array, "b")', ['array' => new Collection(['a' => null])]));
    }

    public function test_array_flatten()
    {
        $array = ['#foo', '#bar', '#baz'];
        $this->assertEquals(['#foo', '#bar', '#baz'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));

        $array = [['#foo', '#bar'], '#baz'];
        $this->assertEquals(['#foo', '#bar', '#baz'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));

        $array = [['#foo', '#bar'], ['#baz']];
        $this->assertEquals(['#foo', '#bar', '#baz'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));

        $array = [['#foo', ['#bar']], ['#baz']];
        $this->assertEquals(['#foo', '#bar', '#baz'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));

        $array = [new Collection(['#foo', '#bar']), ['#baz']];
        $this->assertEquals(['#foo', '#bar', '#baz'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));

        $array = [new Collection(['#foo', ['#bar']]), ['#baz']];
        $this->assertEquals(['#foo', '#bar', '#baz'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));

        $array = [['#foo', new Collection(['#bar'])], ['#baz']];
        $this->assertEquals(['#foo', '#bar', '#baz'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));

        $array = [['#foo', new Collection(['#bar', ['#zap']])], ['#baz']];
        $this->assertEquals(['#foo', '#bar', '#zap', '#baz'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));
    }

    public function test_array_flatten_with_depth()
    {
        $array = [['#foo', ['#bar', ['#baz']]], '#zap'];
        $this->assertEquals(['#foo', '#bar', '#baz', '#zap'], $this->evaluateRaw('arr.flatten(test)', ['test' => $array]));

        $array = [['#foo', ['#bar', ['#baz']]], '#zap'];
        $this->assertEquals(['#foo', ['#bar', ['#baz']], '#zap'], $this->evaluateRaw('arr.flatten(test, 1)', ['test' => $array]));

        $array = [['#foo', ['#bar', ['#baz']]], '#zap'];
        $this->assertEquals(['#foo', '#bar', ['#baz'], '#zap'], $this->evaluateRaw('arr.flatten(test, depth_param)', ['test' => $array, 'depth_param' => 2]));
    }

    public function test_array_forget()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        [$result, $runtimeData] = $this->evaluateBoth('arr.forget(test, null)', ['test' => $array]);
        $this->assertEquals($array, $runtimeData['test']);

        $array = ['products' => ['desk' => ['price' => 100]]];
        [$result, $runtimeData] = $this->evaluateBoth('arr.forget(test, arr.explode(",", ""))', ['test' => $array]);
        $this->assertEquals($array, $runtimeData['test']);

        $array = ['products' => ['desk' => ['price' => 100]]];
        [$result, $runtimeData] = $this->evaluateBoth('arr.forget(test, "products.desk")', ['test' => $array]);
        $this->assertEquals(['products' => []], $runtimeData['test']);

        $array = ['products' => ['desk' => ['price' => 100]]];
        [$result, $runtimeData] = $this->evaluateBoth('arr.forget(test, "products.desk.price")', ['test' => $array]);
        $this->assertEquals(['products' => ['desk' => []]], $runtimeData['test']);
    }

    public function test_array_only()
    {
        $array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
        $this->assertEquals(['name' => 'Desk', 'price' => 100], $this->evaluateRaw('arr.only(test, keys)', [
            'test' => $array, 'keys' => ['name', 'price'],
        ]));

        $array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
        $this->assertEquals(['name' => 'Desk', 'price' => 100],
            $this->evaluateRaw('arr.only(test, arr.explode(",", "name,price"))', ['test' => $array])
        );

        $array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
        $this->assertEmpty($this->evaluateRaw('arr.only(test, keys)', [
            'test' => $array, 'keys' => ['nonExistingKey'],
        ]));
    }

    public function test_array_pluck()
    {
        $data = [
            'post-1' => [
                'comments' => [
                    'tags' => [
                        '#foo', '#bar',
                    ],
                ],
            ],
            'post-2' => [
                'comments' => [
                    'tags' => [
                        '#baz',
                    ],
                ],
            ],
        ];

        $this->assertEquals([
            0 => [
                'tags' => [
                    '#foo', '#bar',
                ],
            ],
            1 => [
                'tags' => [
                    '#baz',
                ],
            ],
        ], $this->evaluateRaw('arr.pluck(test, "comments")', ['test' => $data]));

        $this->assertEquals([['#foo', '#bar'], ['#baz']], $this->evaluateRaw('arr.pluck(test, "comments.tags")', [
            'test' => $data,
        ]));

        $this->assertEquals([null, null], $this->evaluateRaw('arr.pluck(test, "foo")', [
            'test' => $data,
        ]));
        $this->assertEquals([null, null], $this->evaluateRaw('arr.pluck(test, "comments.foo.bar")', [
            'test' => $data,
        ]));
    }

    public function test_array_pluck_with_array_value()
    {
        $array = [
            ['developer' => ['name' => 'Name One']],
            ['developer' => ['name' => 'Name Two']],
        ];

        $this->assertEquals(['Name One', 'Name Two'], $this->evaluateRaw('arr.pluck(test, keys)', [
            'test' => $array, 'keys' => ['developer', 'name'],
        ]));
    }

    public function test_array_pluck_with_keys()
    {
        $array = [
            ['name' => 'Name One', 'role' => 'developer'],
            ['name' => 'Name Two', 'role' => 'developer'],
        ];

        $test1 = $this->evaluateRaw('arr.pluck(test, "role", "name")', ['test' => $array]);
        $test2 = $this->evaluateRaw('arr.pluck(test, null, "name")', ['test' => $array]);

        $this->assertEquals([
            'Name One' => 'developer',
            'Name Two' => 'developer',
        ], $test1);

        $this->assertEquals([
            'Name One' => ['name' => 'Name One', 'role' => 'developer'],
            'Name Two' => ['name' => 'Name Two', 'role' => 'developer'],
        ], $test2);
    }

    public function test_array_prepend()
    {
        $this->assertEquals(['zero', 'one', 'two', 'three', 'four'], $this->evaluateRaw(
            'arr.prepend(arr.explode(",", "one,two,three,four"), "zero")'
        ));

        $array = ['one', 'two', 'three', 'four'];
        $this->assertEquals(['zero', 'one', 'two', 'three', 'four'], $this->evaluateRaw(
            'arr.prepend(test, "zero")', [
                'test' => $array,
            ]
        ));

        $array = ['one' => 1, 'two' => 2];
        $this->assertEquals(['zero' => 0, 'one' => 1, 'two' => 2], $this->evaluateRaw(
            'arr.prepend(test, 0, "zero")',
            ['test' => $array]
        ));
    }

    public function test_array_pull()
    {
        $array = ['name' => 'Desk', 'price' => 100];
        [$result, $runtimeData] = $this->evaluateBoth('arr.pull(test, "name")', [
            'test' => $array,
        ]);
        $this->assertSame('Desk', $result);
        $this->assertSame(['price' => 100], $runtimeData['test']);
    }

    public function test_array_shuffle()
    {
        $array = range(0, 100, 10);
        $this->assertEquals(Arr::shuffle($array, 1234), $this->evaluateRaw('arr.shuffle(array, 1234)', [
            'array' => $array,
        ]));
    }

    public function test_array_query()
    {
        $this->assertSame('', $this->evaluateRaw('arr.query(array)', ['array' => []]));
        $this->assertSame('foo=bar', $this->evaluateRaw('arr.query(array)',
            ['array' => ['foo' => 'bar']])
        );
        $this->assertSame('foo=bar&bar=baz', $this->evaluateRaw('arr.query(array)',
            ['array' => ['foo' => 'bar', 'bar' => 'baz']])
        );
        $this->assertSame('foo=bar&bar=1', $this->evaluateRaw('arr.query(array)',
            ['array' => ['foo' => 'bar', 'bar' => true]])
        );
        $this->assertSame('foo=bar', $this->evaluateRaw('arr.query(array)',
            ['array' => ['foo' => 'bar', 'bar' => null]])
        );
        $this->assertSame('foo=bar&bar=', $this->evaluateRaw('arr.query(array)',
            ['array' => ['foo' => 'bar', 'bar' => '']])
        );
    }

    public function test_array_wrap()
    {
        $object = new \stdClass();
        $object->value = 'a';

        $data = [
            'string' => 'a',
            'array' => ['a'],
            'object' => $object,
        ];

        $this->assertEquals(['a'], $this->evaluateRaw('arr.wrap(string)', $data));
        $this->assertEquals(['a'], $this->evaluateRaw('arr.wrap(array)', $data));
        $this->assertEquals([$object], $this->evaluateRaw('arr.wrap(object)', $data));
    }

    public function test_array_set()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        [$results, $runtimeData] = $this->evaluateBoth('arr.set(test, "products.desk.price", 200)', [
            'test' => $array,
        ]);

        $this->assertEquals(['products' => ['desk' => ['price' => 200]]], $results);

        $array = ['products' => ['desk' => ['price' => 100]]];
        [$results, $runtimeData] = $this->evaluateBoth('arr.set(test, null, array_arg)', [
            'test' => $array, 'array_arg' => ['price' => 300],
        ]);

        $this->assertSame(['price' => 300], $results);
    }

    public function test_array_push()
    {
        [$results, $runtimeData] = $this->evaluateBoth('arr.push(test, value)', [
            'test' => ['one', 'two'],
            'value' => 'three',
        ]);
        // Make sure that the reference value change was bubbled up.
        $this->assertEquals(['one', 'two', 'three'], $runtimeData['test']);
        // Make sure the copy returned contains the same values.
        $this->assertEquals(['one', 'two', 'three'], $results);

        [$results, $runtimeData] = $this->evaluateBoth('arr.push(test, "three", "four")', [
            'test' => ['one', 'two'],
        ]);
        // Make sure that the reference value change was bubbled up.
        $this->assertEquals(['one', 'two', 'three', 'four'], $runtimeData['test']);
        // Make sure the copy returned contains the same values.
        $this->assertEquals(['one', 'two', 'three', 'four'], $results);

        // Test that arrays can be added.
        [$results, $runtimeData] = $this->evaluateBoth('arr.push(test, arr_var)', [
            'test' => ['one', 'two'],
            'arr_var' => ['three', 'four'],
        ]);
        $this->assertEquals(['one', 'two', ['three', 'four']], $results);
        $this->assertEquals(['one', 'two', ['three', 'four']], $runtimeData['test']);
    }

    public function test_array_keys()
    {
        $this->assertEquals(['one', 'two'], $this->evaluateRaw('arr.keys(test)', [
            'test' => ['one' => 1, 'two' => 2],
        ]));
    }

    public function test_array_key_exists()
    {
        $this->assertEquals(true, $this->evaluateRaw('arr.keyExists("one", test)', [
            'test' => ['one' => 1, 'two' => 2],
        ]));

        $this->assertEquals(false, $this->evaluateRaw('arr.keyExists("four", test)', [
            'test' => ['one' => 1, 'two' => 2],
        ]));
    }

    public function test_array_values()
    {
        $this->assertEquals([1, 2], $this->evaluateRaw('arr.values(test)', [
            'test' => ['one' => 1, 'two' => 2],
        ]));
    }

    public function test_array_min()
    {
        $this->assertEquals(1, $this->evaluateRaw('arr.min(values)', [
            'values' => [10, 3, 1, 5, 323],
        ]));
    }

    public function test_array_max()
    {
        $this->assertEquals(323, $this->evaluateRaw('arr.max(values)', [
            'values' => [10, 3, 1, 5, 323],
        ]));
    }

    public function test_array_reverse()
    {
        $array = ['one', 'two', 'three'];
        $this->assertEquals(
            array_reverse($array),
            $this->evaluateRaw('array.reverse(value)', [
                'value' => $array,
            ])
        );

        $array = ['one' => 1, 'two' => 2, 'three' => 3];
        $this->assertEquals(
            array_reverse($array),
            $this->evaluateRaw('array.reverse(value)', [
                'value' => $array,
            ])
        );

        $array = ['one' => 1, 'two' => 2, 'three' => 3];
        $this->assertEquals(
            array_reverse($array, true),
            $this->evaluateRaw('array.reverse(value, true)', [
                'value' => $array,
            ])
        );
    }

    public function test_array_count_values()
    {
        $array = ['one' => 1, 'two' => 2, 'three' => 3];
        $this->assertEquals(
            array_count_values($array),
            $this->evaluateRaw('array.countValues(value)', [
                'value' => $array,
            ])
        );
    }

    public function test_array_sort()
    {
        $array = ['lemon', 'orange', 'banana', 'apple'];
        $array_php = ['lemon', 'orange', 'banana', 'apple'];
        [$results, $runtimeData] = $this->evaluateBoth('arr.sort(test)', ['test' => $array]);
        sort($array_php);
        $this->assertEquals($array_php, $results);
        $this->assertEquals($array_php, $runtimeData['test']);
    }

    public function test_array_asort()
    {
        $fruits = ['d' => 'lemon', 'a' => 'orange', 'b' => 'banana', 'c' => 'apple'];
        $fruits_php = ['d' => 'lemon', 'a' => 'orange', 'b' => 'banana', 'c' => 'apple'];

        [$results, $runtimeData] = $this->evaluateBoth('arr.asort(test)', ['test' => $fruits]);
        asort($fruits_php);
        $this->assertEquals($fruits_php, $results);
        $this->assertEquals($fruits_php, $runtimeData['test']);
    }

    public function test_array_rsort()
    {
        $fruits = ['lemon', 'orange', 'banana', 'apple'];
        $fruits_php = ['lemon', 'orange', 'banana', 'apple'];

        [$results, $runtimeData] = $this->evaluateBoth('arr.rsort(test)', ['test' => $fruits]);
        rsort($fruits_php);
        $this->assertEquals($fruits_php, $results);
        $this->assertEquals($fruits_php, $runtimeData['test']);
    }

    public function test_array_arsort()
    {
        $fruits = ['d' => 'lemon', 'a' => 'orange', 'b' => 'banana', 'c' => 'apple'];
        $fruits_php = ['d' => 'lemon', 'a' => 'orange', 'b' => 'banana', 'c' => 'apple'];

        [$results, $runtimeData] = $this->evaluateBoth('arr.arsort(test)', ['test' => $fruits]);
        arsort($fruits_php);
        $this->assertEquals($fruits_php, $results);
        $this->assertEquals($fruits_php, $runtimeData['test']);
    }

    public function test_array_diff()
    {
        $array1 = ['a' => 'green', 'red', 'blue', 'red'];
        $array2 = ['b' => 'green', 'yellow', 'red'];

        $results = $this->evaluateRaw('arr.diff(array1, array2)', [
            'array1' => $array1,
            'array2' => $array2,
        ]);

        $this->assertSame(array_diff($array1, $array2), $results);
    }

    public function test_array_unique()
    {
        $array = ['one', 'two', 'two', 'three', 'three', 'three'];

        $this->assertSame(array_unique($array), $this->evaluateRaw('arr.unique(test)', [
            'test' => $array,
        ]));
    }

    public function test_array_search()
    {
        $array = [0 => 'blue', 1 => 'red', 2 => 'green', 3 => 'red'];

        $this->assertSame(2, $this->evaluateRaw(
            'arr.search("green", test)', ['test' => $array]
        ));

        $this->assertSame(1, $this->evaluateRaw(
            'arr.search("red", test)', ['test' => $array]
        ));
    }

    public function test_in_array()
    {
        $this->assertSame(true, $this->evaluateRaw(
            'arr.inArray("one", test)', [
                'test' => ['one', 'two', 'three'],
            ]
        ));

        $this->assertSame(false, $this->evaluateRaw(
            'arr.inArray("four", test)', [
                'test' => ['one', 'two', 'three'],
            ]
        ));
    }

    public function test_implode()
    {
        $this->assertSame('one,two,three', $this->evaluateRaw(
            'arr.implode(",", test)', [
                'test' => ['one', 'two', 'three'],
            ]
        ));
    }

    public function test_slice()
    {
        $array = ['one' => 1, 'two' => 2, 'three' => 3];
        $result = $this->evaluateRaw('arr.slice(test, 1, 2)', ['test' => $array]);
        $this->assertSame(array_slice($array, 1, 2), $result);
    }

    public function test_slice_with_named_arguments()
    {
        $array = ['one' => 1, 'two' => 2, 'three' => 3];
        $result = $this->evaluateRaw('arr.slice(test, 2, preserve_keys: true)', ['test' => $array]);
        $this->assertSame(array_slice($array, 2, null, true), $result);
    }

    public function test_array_flip()
    {
        $array = ['one' => 1, 'two' => 2, 3 => 'three'];
        $this->assertSame(array_flip($array), $this->evaluateRaw('arr.flip(test)', ['test' => $array]));
    }

    public function test_array_shift()
    {
        $fruit = ['orange', 'banana', 'apple', 'raspberry'];

        [$results, $runtimeData] = $this->evaluateBoth('arr.shift(test)', ['test' => $fruit]);
        $this->assertEquals('orange', $results);
        $this->assertEquals(['banana', 'apple', 'raspberry'], $runtimeData['test']);
    }

    public function test_array_unshift()
    {
        $fruits = ['orange', 'banana'];
        [$results, $runtimeData] = $this->evaluateBoth('arr.unshift(test, "apple", "raspberry")', ['test' => $fruits]);

        $expected = ['apple', 'raspberry', 'orange', 'banana'];
        $this->assertSame($expected, $results);
        $this->assertSame($expected, $runtimeData['test']);
    }

    public function test_array_splice()
    {
        $colors = ['red', 'green', 'blue', 'yellow'];
        [$results, $runtimeData] = $this->evaluateBoth('array.splice(test, 2)', ['test' => $colors]);
        $this->assertEquals(['red', 'green'], $runtimeData['test']);
        $this->assertEquals(['blue', 'yellow'], $results);
    }

    public function test_array_pop()
    {
        $colors = ['red', 'green', 'blue', 'yellow'];

        [$results, $runtimeData] = $this->evaluateBoth('array.pop(test)', ['test' => $colors]);
        $this->assertSame('yellow', $results);
        $this->assertSame(['red', 'green', 'blue'], $runtimeData['test']);
    }

    public function test_array_merge()
    {
        $array1 = ['one', 'two'];
        $array2 = ['three', 'four'];
        $array3 = ['five', 'six'];

        $results = $this->evaluateRaw('arr.merge(array1, array2, array3)', [
            'array1' => $array1, 'array2' => $array2, 'array3' => $array3,
        ]);

        $this->assertSame(array_merge($array1, $array2, $array3), $results);
    }

    public function test_array_intersect()
    {
        $array1 = ['one', 'two'];
        $array2 = ['three', 'four'];
        $array3 = ['five', 'six'];

        $results = $this->evaluateRaw('arr.intersect(array1, array2, array3)', [
            'array1' => $array1, 'array2' => $array2, 'array3' => $array3,
        ]);

        $this->assertSame(array_intersect($array1, $array2, $array3), $results);
    }

    public function test_array_ksort()
    {
        $fruits = ['d'=>'lemon', 'a'=>'orange', 'b'=>'banana', 'c'=>'apple'];
        $fruits_php = ['d'=>'lemon', 'a'=>'orange', 'b'=>'banana', 'c'=>'apple'];
        ksort($fruits_php);

        [$results, $runtimeData] = $this->evaluateBoth('array.ksort(test)', ['test' => $fruits]);
        $this->assertSame(true, $results);
        $this->assertSame($fruits_php, $runtimeData['test']);
    }

    public function test_array_natural_sort()
    {
        $data = ['img12.png', 'img10.png', 'img2.png', 'img1.png'];
        $data_php = ['img12.png', 'img10.png', 'img2.png', 'img1.png'];
        natsort($data_php);

        [$results, $runtimeData] = $this->evaluateBoth('array.naturalSort(test)', ['test' => $data]);
        $this->assertSame(true, $results);
        $this->assertSame($data_php, $runtimeData['test']);
    }
}
