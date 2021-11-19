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

    /** @test */
    public function results_are_found_using_where_between()
    {
        $items = collect([
            ['reference' => 'a', 'number_field' => 8],
            ['reference' => 'b', 'number_field' => 9],
            ['reference' => 'c', 'number_field' => 10],
            ['reference' => 'd', 'number_field' => 11],
            ['reference' => 'e', 'number_field' => 12],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereBetween('number_field', [9, 11])->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['b', 'c', 'd'], $results->map->reference->all());
    }

    /** @test */
    public function results_are_found_using_where_not_between()
    {
        $items = collect([
            ['reference' => 'a', 'number_field' => 8],
            ['reference' => 'b', 'number_field' => 9],
            ['reference' => 'c', 'number_field' => 10],
            ['reference' => 'd', 'number_field' => 11],
            ['reference' => 'e', 'number_field' => 12],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereNotBetween('number_field', [9, 11])->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'e'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_or_where_between()
    {
        $this->markTestSkipped();
    }

    /** @test **/
    public function results_are_found_using_or_where_not_between()
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
