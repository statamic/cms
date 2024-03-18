<?php

namespace Tests\Query;

use Statamic\Contracts\Query\Builder;
use Statamic\Query\StatusQueryBuilder;
use Tests\TestCase;

class StatusQueryBuilderTest extends TestCase
{
    /** @test */
    public function it_implements_query_builder()
    {
        $this->assertInstanceOf(Builder::class, new StatusQueryBuilder($this->mock(Builder::class)));
    }

    /** @test */
    public function it_proxies_methods_onto_the_builder()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('where')->with('foo', 'bar')->andReturnSelf();
        $builder->shouldReceive('where')->with('status', 'baz')->andReturnSelf();
        $builder->shouldReceive('limit')->with(3)->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($expected = collect([
            ['id' => 'foo'],
            ['id' => 'bar'],
            ['id' => 'baz'],
        ]));

        $results = (new StatusQueryBuilder($builder))
            ->where('foo', 'bar')
            ->where('status', 'baz')
            ->limit(3)
            ->get(['foo', 'bar']);

        $this->assertEquals($expected, $results);
    }

    /** @test */
    public function it_queries_status_by_default()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('where')->with('status', 'published')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn('results');

        $results = (new StatusQueryBuilder($builder))->get();

        $this->assertEquals('results', $results);
    }

    /** @test */
    public function the_fallback_query_status_value_can_be_set_in_the_constructor()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('where')->with('status', 'potato')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn('results');

        $results = (new StatusQueryBuilder($builder, 'potato'))->get();

        $this->assertEquals('results', $results);
    }

    /**
     * @test
     *
     * @dataProvider statusQueryMethodProvider
     */
    public function it_doesnt_perform_fallback_status_query_when_status_is_explicitly_queried($method)
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive($method)->with('status', 'foo')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn('results');

        $query = (new StatusQueryBuilder($builder));

        $query->$method('status', 'foo');

        $this->assertEquals('results', $query->get());
    }

    /**
     * @test
     *
     * @dataProvider statusQueryMethodProvider
     */
    public function it_doesnt_perform_fallback_status_query_when_published_is_explicitly_queried($method)
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive($method)->with('published', 'foo')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn('results');

        $query = (new StatusQueryBuilder($builder));

        $query->$method('published', 'foo');

        $this->assertEquals('results', $query->get());
    }

    public static function statusQueryMethodProvider()
    {
        return collect([
            'where',
            'whereIn',
            'whereNotIn',
            'whereNull',
            'whereNotNull',
            'orWhere',
            'orWhereIn',
            'orWhereNotIn',
            'orWhereNull',
            'orWhereNotNull',
        ])->mapWithKeys(fn ($v) => [$v => [$v]])->all();
    }
}
