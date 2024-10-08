<?php

namespace Tests\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Query\OrderBy;
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
}
