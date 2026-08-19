<?php

namespace Tests\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Query\OrderBy;
use Statamic\Tags\Concerns\QueriesOrderBys;
use Tests\TestCase;

class OrderByTest extends TestCase
{
    #[Test]
    #[DataProvider('parseProvider')]
    public function it_parses_string($string, $sort, $dir)
    {
        $orderby = OrderBy::parse($string);

        $this->assertEquals($sort, $orderby->sort);
        $this->assertEquals($dir, $orderby->direction);
    }

    public static function parseProvider()
    {
        return [
            ['foo', 'foo', 'asc'],
            ['foo:asc', 'foo', 'asc'],
            ['foo:desc', 'foo', 'desc'],

            ['foo:bar', 'foo->bar', 'asc'],
            ['foo:bar:asc', 'foo->bar', 'asc'],
            ['foo:bar:desc', 'foo->bar', 'desc'],

            ['foo:bar:baz', 'foo->bar->baz', 'asc'],
            ['foo:bar:baz:asc', 'foo->bar->baz', 'asc'],
            ['foo:bar:baz:desc', 'foo->bar->baz', 'desc'],
        ];
    }

    #[Test]
    #[DataProvider('columnProvider')]
    public function it_validates_columns($column, $expected)
    {
        $this->assertEquals($expected, OrderBy::column($column));
        $this->assertEquals($expected ?? 'fallback', OrderBy::column($column, 'fallback'));
    }

    public static function columnProvider()
    {
        return [
            'simple' => ['title', 'title'],
            'with_underscores' => ['first_name', 'first_name'],
            'with_numbers' => ['field1', 'field1'],
            'json_arrow' => ['data->title', 'data->title'],
            'nested_json_arrow' => ['data->nested->field', 'data->nested->field'],
            'dotted' => ['table.column', 'table.column'],
            'single_quote' => ["title'", null],
            'double_quote' => ['title"', null],
            'semicolon' => ['title;', null],
            'space' => ['title foo', null],
            'parentheses' => ['title()', null],
            'sql_injection' => ["title'; DROP TABLE entries;--", null],
            'backtick' => ['title`', null],
            'comma' => ['title,foo', null],
            'empty_string' => ['', null],
            'null' => [null, null],
        ];
    }

    #[Test]
    public function it_filters_unsafe_columns_from_order_bys()
    {
        $tag = new class
        {
            use QueriesOrderBys;

            public $params = [];
        };

        $tag->params = ['sort' => "title|foo'; DROP TABLE entries;--|date"];

        $method = new \ReflectionMethod($tag, 'parseOrderBys');
        $orderBys = $method->invoke($tag);

        $this->assertEquals(['title', 'date'], $orderBys->map->sort->values()->all());
    }
}
