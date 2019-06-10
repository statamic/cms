<?php

namespace Tests;

use Statamic\API\YAML;

class YAMLTest extends TestCase
{
    /** @test */
    function it_parses_yaml()
    {
        $this->assertEquals(['foo' => 'bar'], YAML::parse('foo: bar'));
    }

    /** @test */
    function it_parses_yaml_with_front_matter()
    {
        $string = '---
foo: bar
baz: qux
---
this is the content';

        $expected = [
            'foo' => 'bar',
            'baz' => 'qux',
            'content' => 'this is the content',
        ];

        $this->assertEquals($expected, YAML::parse($string));
    }

    /** @test */
    function it_dumps_yaml()
    {
        $expected = '---
foo: bar
baz: qux
array:
  hello: world
---
';

        $array = [
            'foo' => 'bar',
            'baz' => 'qux',
            'array' => [
                'hello' => 'world',
            ]
        ];

        $this->assertEquals($expected, YAML::dump($array));
    }

    /** @test */
    function it_dumps_yaml_with_content()
    {
        $expected = '---
foo: bar
baz: qux
array:
  hello: world
---
this is the content';

        $array = [
            'foo' => 'bar',
            'baz' => 'qux',
            'array' => [
                'hello' => 'world',
            ]
        ];

        $this->assertEquals($expected, YAML::dump($array, 'this is the content'));
    }
}