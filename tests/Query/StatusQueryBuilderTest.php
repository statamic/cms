<?php

namespace Tests\Query;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Query\Builder;
use Statamic\Query\StatusQueryBuilder;
use Tests\TestCase;

class StatusQueryBuilderTest extends TestCase
{
    #[Test]
    public function it_implements_query_builder()
    {
        $this->assertInstanceOf(Builder::class, new StatusQueryBuilder($this->mock(Builder::class)));
    }

    #[Test]
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

    #[Test]
    public function it_queries_status_by_default()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('whereStatus')->with('published')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn('results');

        $results = (new StatusQueryBuilder($builder))->get();

        $this->assertEquals('results', $results);
    }

    #[Test]
    public function the_fallback_query_status_value_can_be_set_in_the_constructor()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('whereStatus')->with('potato')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn('results');

        $results = (new StatusQueryBuilder($builder, 'potato'))->get();

        $this->assertEquals('results', $results);
    }

    #[Test]
    #[DataProvider('queryExecutingMethodProvider')]
    public function it_queries_status_by_default_before_executing_the_query($method, $parameters)
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('whereStatus')->with('published')->once()->ordered()->andReturnSelf();
        $builder->shouldReceive($method)->with(...$parameters)->once()->ordered()->andReturn('results');

        $results = (new StatusQueryBuilder($builder))->$method(...$parameters);

        $this->assertEquals('results', $results);
    }

    public static function queryExecutingMethodProvider()
    {
        return [
            'count' => ['count', []],
            'exists' => ['exists', []],
            'pluck' => ['pluck', ['id']],
            'paginate' => ['paginate', [10]],
            'find' => ['find', ['foo']],
            'firstOrFail' => ['firstOrFail', []],
            'firstOr' => ['firstOr', [fn () => 'fallback']],
            'sole' => ['sole', []],
            'min' => ['min', ['foo']],
            'max' => ['max', ['foo']],
            'sum' => ['sum', ['foo']],
            'avg' => ['avg', ['foo']],
            'average' => ['average', ['foo']],
            'chunk' => ['chunk', [10, fn () => null]],
            'lazy' => ['lazy', []],
        ];
    }

    #[Test]
    public function it_only_queries_the_fallback_status_once()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('whereStatus')->with('published')->once()->andReturnSelf();
        $builder->shouldReceive('count')->once()->andReturn(2);
        $builder->shouldReceive('get')->once()->andReturn('results');

        $query = new StatusQueryBuilder($builder);

        $this->assertEquals(2, $query->count());
        $this->assertEquals('results', $query->get());
    }

    #[Test]
    #[DataProvider('statusQueryMethodProvider')]
    public function it_doesnt_perform_fallback_status_query_when_status_is_explicitly_queried($method)
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive($method)->with('status', 'foo')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn('results');

        $query = (new StatusQueryBuilder($builder));

        $query->$method('status', 'foo');

        $this->assertEquals('results', $query->get());
    }

    #[Test]
    public function it_doesnt_perform_fallback_status_query_when_wherestatus_is_explicitly_queried()
    {
        $builder = $this->mock(Builder::class);
        $builder->shouldReceive('whereStatus')->with('foo')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn('results');

        $query = (new StatusQueryBuilder($builder));

        $query->whereStatus('foo');

        $this->assertEquals('results', $query->get());
    }

    #[Test]
    #[DataProvider('statusQueryMethodProvider')]
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
