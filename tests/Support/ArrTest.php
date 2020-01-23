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

    /** @test */
    function it_gets_the_first_non_null_value()
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

    /** @test */
    function it_checks_if_its_empty()
    {
        $this->assertTrue(Arr::isEmpty([])); // completely empty array

        $this->assertFalse(Arr::isEmpty(['foo' => 'bar'])); // definitely not empty

        $this->assertTrue(Arr::isEmpty(['foo' => ''])); // just consists of empty strings
        $this->assertTrue(Arr::isEmpty(['foo' => '', 'bar' => '']));

        $this->assertFalse(Arr::isEmpty(['foo' => null])); // nulls are not empty
        $this->assertFalse(Arr::isEmpty(['foo' => '', 'bar' => null]));

        $this->assertTrue(Arr::isEmpty(['foo' => []])); // recursion
        $this->assertTrue(Arr::isEmpty(['foo' => ['bar' => []]]));
        $this->assertTrue(Arr::isEmpty(['foo' => ['bar' => ['baz' => '']]]));
        $this->assertFalse(Arr::isEmpty(['foo' => ['bar' => ['baz' => 'qux']]]));
        $this->assertFalse(Arr::isEmpty(['foo' => ['bar' => ['baz' => null]]]));
    }
}
