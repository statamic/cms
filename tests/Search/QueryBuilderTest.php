<?php

namespace Tests\Search;

use Statamic\Search\QueryBuilder;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_can_get_results()
    {
        $items = collect([
            ['reference' => 'a'],
            ['reference' => 'b'],
            ['reference' => 'c'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['a', 'b', 'c'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_where()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo'],
            ['reference' => 'b', 'title' => 'Gandalf'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->where('title', 'like', '%Frodo%')->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_or_where()
    {
        $this->markTestSkipped();
    }

    /** @test **/
    public function results_are_found_using_or_where_in()
    {
        $this->markTestSkipped();
    }
}

class FakeQueryBuilder extends QueryBuilder
{
    protected $results;

    public function __construct($results)
    {
        $this->results = $results;
    }

    public function getSearchResults($query)
    {
        return $this->results;
    }
}
