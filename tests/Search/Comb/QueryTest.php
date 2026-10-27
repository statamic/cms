<?php

namespace Tests\Search\Comb;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Comb\Index;
use Statamic\Search\Comb\Query;
use Tests\TestCase;

class QueryTest extends TestCase
{
    #[Test]
    public function paginating_looks_up_the_index_once()
    {
        $index = Mockery::mock(Index::class);
        $index->shouldReceive('lookup')->with('foo')->once()->andReturn(collect([
            ['reference' => 'a', 'search_score' => 2],
            ['reference' => 'b', 'search_score' => 1],
        ]));

        $paginator = (new Query($index))->query('foo')->withoutData()->paginate(1);

        $this->assertEquals(2, $paginator->total());
        $this->assertEquals(['a'], $paginator->getCollection()->map->reference->all());
    }

    #[Test]
    public function changing_the_query_looks_up_the_index_again()
    {
        $index = Mockery::mock(Index::class);
        $index->shouldReceive('lookup')->with('foo')->once()->andReturn(collect([['reference' => 'a']]));
        $index->shouldReceive('lookup')->with('bar')->once()->andReturn(collect([['reference' => 'b']]));

        $builder = (new Query($index))->withoutData();

        $this->assertEquals(['a'], $builder->query('foo')->get()->map->reference->all());
        $this->assertEquals(['b'], $builder->query('bar')->get()->map->reference->all());
        $this->assertEquals(['a'], $builder->query('foo')->get()->map->reference->all());
    }
}
