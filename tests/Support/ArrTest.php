<?php

namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use Statamic\Support\Arr;

class ArrTest extends TestCase
{
    /** @test */
    function it_adds_scope_to_associative_array()
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
            ]
        ];

        $this->assertEquals($expected, Arr::addScope($arr, 'myscope'));
    }

    /** @test */
    function it_adds_scope_to_multidimensional_array()
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

        $expected = [
            [
                'foo' => 'bar',
                'baz' => 'qux',
                'myscope' => [
                    'foo' => 'bar',
                    'baz' => 'qux',
                ]
            ],
            [
                'foo' => 'bar2',
                'baz' => 'qux2',
                'myscope' => [
                    'foo' => 'bar2',
                    'baz' => 'qux2',
                ]
            ],
        ];

        $this->assertEquals($expected, Arr::addScope($arr, 'myscope'));
    }

    /** @test */
    function it_doesnt_add_scope_to_lists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scopes can only be added to associative or multidimensional arrays.');

        Arr::addScope(['one', 'two'], 'scope');
    }
}
