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
    public function entries_are_found_using_where()
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
    public function entries_are_found_using_or_where()
    {
        $this->markTestSkipped();
    }

    /** @test **/
    public function entries_are_found_using_or_where_in()
    {
        $this->markTestSkipped();
    }

    /** @test */
    public function results_are_found_using_where_null()
    {
        $items = collect([
            ['reference' => 'a', 'text' => 'Text 1'],
            ['reference' => 'b', 'text' => 'Text 2'],
            ['reference' => 'c'],
            ['reference' => 'd', 'text' => 'Text 4'],
            ['reference' => 'e'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereNull('text')->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['c', 'e'], $results->map->reference->all());
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

    /** @test **/
    public function entries_are_found_using_where_json_contains()
    {
        $items = collect([
            ['reference' => 'a', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']],
            ['reference' => 'b', 'test_taxonomy' => ['taxonomy-3']],
            ['reference' => 'c', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']],
            ['reference' => 'd', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']],
            ['reference' => 'e', 'test_taxonomy' => ['taxonomy-5']],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereJsonContains('test_taxonomy', ['taxonomy-1', 'taxonomy-5'])->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['a', 'c', 'e'], $results->map->reference->all());
    }

    /** @test **/
    public function entries_are_found_using_where_json_doesnt_contain()
    {
        $items = collect([
            ['reference' => 'a', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']],
            ['reference' => 'b', 'test_taxonomy' => ['taxonomy-3']],
            ['reference' => 'c', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']],
            ['reference' => 'd', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']],
            ['reference' => 'e', 'test_taxonomy' => ['taxonomy-5']],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereJsonDoesntContain('test_taxonomy', ['taxonomy-1'])->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['b', 'd', 'e'], $results->map->reference->all());
    }

    /** @test **/
    public function entries_are_found_using_or_where_json_contains()
    {
        $this->markTestSkipped();
    }

    /** @test **/
    public function entries_are_found_using_or_where_json_doesnt_contain()
    {
        $this->markTestSkipped();
    }

    /** @test **/
    public function entries_are_found_using_where_json_length()
    {
        $items = collect([
            ['reference' => 'a', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']],
            ['reference' => 'b', 'test_taxonomy' => ['taxonomy-3']],
            ['reference' => 'c', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']],
            ['reference' => 'd', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']],
            ['reference' => 'e', 'test_taxonomy' => ['taxonomy-5']],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereJsonLength('test_taxonomy', 1)->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['b', 'e'], $results->map->reference->all());
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
