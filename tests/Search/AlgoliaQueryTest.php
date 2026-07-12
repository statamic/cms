<?php

namespace Tests\Search;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Search\Algolia\Index;
use Statamic\Search\Algolia\Query;
use Tests\TestCase;

class AlgoliaQueryTest extends TestCase
{
    #[Test]
    public function it_adds_scores()
    {
        $index = Mockery::mock(Index::class);
        $index->shouldReceive('name');
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
