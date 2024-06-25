<?php

namespace Tests\Support;

use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Statamic\Fields\Values;
use Statamic\Support\Arr;

class ArrTest extends TestCase
{
    use Concerns\TestsIlluminateArr;

    #[Test]
    public function it_adds_scope_to_associative_array()
    {
        $arr = [
            'foo' => 'bar',
            'baz' => 'qux',
        ];

        $expected = [
            'foo' => 'bar',
            'baz' => 'qux',
            'myscope' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ];

        $this->assertEquals($expected, Arr::addScope($arr, 'myscope'));
    }

    #[Test]
    #[DataProvider('multiDimensionalArrayScopeProvider')]
    public function it_adds_scope_to_multidimensional_array($mapInto)
    {
        $arr = [
            [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            [
                'foo' => 'bar2',
                'baz' => 'qux2',
            ],
        ];

        if ($mapInto) {
            // Convert the inner array into something (like Collection or Values objects)
            $arr = collect($arr)->mapInto($mapInto)->all();
        }

        $expected = [
            [
                'foo' => 'bar',
                'baz' => 'qux',
                'myscope' => [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ],
            ],
            [
                'foo' => 'bar2',
                'baz' => 'qux2',
                'myscope' => [
                    'foo' => 'bar2',
                    'baz' => 'qux2',
                ],
            ],
        ];

        $this->assertEquals($expected, Arr::addScope($arr, 'myscope'));
    }

    public static function multiDimensionalArrayScopeProvider()
    {
        return [
            'array' => [null],
            'collection' => [Collection::class],
            'values' => [Values::class],
        ];
    }

    #[Test]
    public function it_doesnt_add_scope_to_lists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scopes can only be added to associative or multidimensional arrays.');

        Arr::addScope(['one', 'two'], 'scope');
    }

    #[Test]
    public function it_gets_the_first_non_null_value()
    {
        $this->assertEquals('one', Arr::getFirst([
            'foo' => 'one',
            'bar' => 'two',
        ], ['foo', 'bar']));

        $this->assertEquals('two', Arr::getFirst([
            'foo' => null,
            'bar' => 'two',
        ], ['foo', 'bar']));

        $this->assertEquals(false, Arr::getFirst([
            'foo' => false,
            'bar' => 'two',
        ], ['foo', 'bar']));
    }
}
