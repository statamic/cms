<?php

namespace Tests\Query;

use Statamic\Contracts\Query\Builder;
use Statamic\Query\OrderedQueryBuilder;
use Tests\TestCase;

class OrderedQueryBuilderTest extends TestCase
{
    /** @test */
    public function it_implements_query_builder()
    {
        $this->assertInstanceOf(Builder::class, new OrderedQueryBuilder($this->mock(Builder::class)));
    }

    /** @test */
    public function it_proxies_methods_onto_the_builder()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('where')->with('foo', 'bar')->andReturnSelf();
        $builder->shouldReceive('where')->with('bar', 'baz')->andReturnSelf();
        $builder->shouldReceive('limit')->with(3)->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($expected = collect([
            ['id' => 'foo'],
            ['id' => 'bar'],
            ['id' => 'baz'],
        ]));

        $results = (new OrderedQueryBuilder($builder))
            ->where('foo', 'bar')
            ->where('bar', 'baz')
            ->limit(3)
            ->get(['foo', 'bar']);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function it_orders_the_items_after_getting_them()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('get')->once()->andReturn(collect([
            ['id' => '1'],
            ['id' => '2'],
            ['id' => '3'],
            ['id' => '4'],
            ['id' => '5'],
        ]));

        $results = (new OrderedQueryBuilder($builder, [4, 5, 2]))->get();

        $this->assertEquals([
            ['id' => '4'],
            ['id' => '5'],
            ['id' => '2'],
            ['id' => '1'], // Not in the provided order array so it goes to the end
            ['id' => '3'], //
        ], $results->all());
    }

    /** @test */
    public function it_wont_order_the_items_after_getting_them_if_the_builder_is_manually_ordered()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('orderBy')->with('title')->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn(collect([
            ['id' => '3'],
            ['id' => '1'],
            ['id' => '2'],
        ]));

        $results = (new OrderedQueryBuilder($builder, [2, 3, 1]))->orderBy('title')->get();

        $this->assertEquals([
            ['id' => '3'],
            ['id' => '1'],
            ['id' => '2'],
        ], $results->all());
    }

    /** @test */
    public function it_wont_order_the_items_when_using_pagination()
    {
        // This will just be a known limitation.
        // Since order matters when using pagination, we can't change it after the page has been retrieved.

        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('paginate')->once()->andReturn('paginator');

        $results = (new OrderedQueryBuilder($builder, [2, 3, 1]))->paginate();

        $this->assertEquals('paginator', $results);
    }
}
