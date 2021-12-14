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
    public function results_are_found_using_where_not_null()
    {
        $items = collect([
            ['reference' => 'a', 'text' => 'Text 1'],
            ['reference' => 'b', 'text' => 'Text 2'],
            ['reference' => 'c'],
            ['reference' => 'd', 'text' => 'Text 4'],
            ['reference' => 'e'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereNotNull('text')->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['a', 'b', 'd'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_or_where_null()
    {
        $this->markTestSkipped();
    }

    /** @test **/
    public function results_are_found_using_or_where_not_null()
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

    /** @test **/
    public function results_are_found_using_nested_where()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo'],
            ['reference' => 'b', 'title' => 'Gandalf'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious'],
            ['reference' => 'e', 'title' => 'Sauron'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()
            ->where(function ($query) {
                $query->where('title', 'Frodo');
            })
            ->orWhere(function ($query) {
                $query->where('title', 'Gandalf')
                    ->orWhere('title', 'Smeagol\'s Precious');
            })
            ->orWhere('title', 'Sauron')
            ->get();

        $this->assertCount(4, $results);
        $this->assertEquals(['a', 'b', 'd', 'e'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_nested_where_in()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo'],
            ['reference' => 'b', 'title' => 'Gandalf'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious'],
            ['reference' => 'e', 'title' => 'Sauron'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()
            ->where(function ($query) {
                $query->where('title', 'Frodo');
            })
            ->orWhere(function ($query) {
                $query->whereIn('title', ['Frodo', 'Smeagol\'s Precious'])->orWhereIn('title', ['Frodo\'s Precious']);
            })
            ->orWhere('title', 'Sauron')
            ->get();

        $this->assertCount(4, $results);
        $this->assertEquals(['a', 'd', 'c', 'e'], $results->map->reference->all());
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
