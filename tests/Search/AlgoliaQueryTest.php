<?php

namespace Tests\Search;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Algolia\Index;
use Statamic\Search\Algolia\Query;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

class AlgoliaQueryTest extends TestCase
{
    #[Test]
    public function it_adds_scores()
    {
        $index = Mockery::mock(Index::class);
        $index->shouldReceive('searchUsingApi')->with('foo')->once()->andReturn(collect([
            ['reference' => 'a'],
            ['reference' => 'b'],
            ['reference' => 'c'],
        ]));

        $query = new Query($index);

        $this->assertEquals([
            ['reference' => 'a', 'search_score' => 3],
            ['reference' => 'b', 'search_score' => 2],
            ['reference' => 'c', 'search_score' => 1],
        ], $query->getSearchResults('foo')->all());
    }

    #[Test]
    public function it_can_get_results_after_adding_prefix()
    {
        $index = Mockery::mock(Index::class);

        Config::set('statamic.search.drivers.algolia.prefix', 'env_');

        $index->shouldReceive('searchUsingApi')->with('foo')->once()->andReturn(collect([
            ['reference' => 'a'],
            ['reference' => 'b'],
            ['reference' => 'c'],
        ]));

        $query = new Query($index);

        $this->assertEquals([
            ['reference' => 'a', 'search_score' => 3],
            ['reference' => 'b', 'search_score' => 2],
            ['reference' => 'c', 'search_score' => 1],
        ], $query->getSearchResults('foo')->all());
    }
}
