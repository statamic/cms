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

    /** @test **/
    public function results_are_found_using_where_date()
    {
        $items = collect([
            ['reference' => 'a', 'test_date' => 1637008264],
            ['reference' => 'b', 'test_date' => '2021-11-14 09:00:00'],
            ['reference' => 'c', 'test_date' => '2021-11-15'],
            ['reference' => 'd', 'test_date' => 1627008264],
            ['reference' => 'e', 'test_date' => null],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereDate('test_date', '2021-11-15')->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereDate('test_date', 1637000264)->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereDate('test_date', '>=', '2021-11-15')->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_where_month()
    {
        $items = collect([
            ['reference' => 'a', 'test_date' => 1637008264],
            ['reference' => 'b', 'test_date' => '2021-11-14 09:00:00'],
            ['reference' => 'c', 'test_date' => '2021-11-15'],
            ['reference' => 'd', 'test_date' => 1627008264],
            ['reference' => 'e', 'test_date' => null],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereMonth('test_date', 11)->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['a', 'b', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereMonth('test_date', '<', 11)->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['d'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_where_day()
    {
        $items = collect([
            ['reference' => 'a', 'test_date' => 1637008264],
            ['reference' => 'b', 'test_date' => '2021-11-14 09:00:00'],
            ['reference' => 'c', 'test_date' => '2021-11-15'],
            ['reference' => 'd', 'test_date' => 1627008264],
            ['reference' => 'e', 'test_date' => null],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereDay('test_date', 15)->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereDay('test_date', '<', 15)->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['b'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_where_year()
    {
        $items = collect([
            ['reference' => 'a', 'test_date' => 1637008264],
            ['reference' => 'b', 'test_date' => '2021-11-14 09:00:00'],
            ['reference' => 'c', 'test_date' => '2021-11-15'],
            ['reference' => 'd', 'test_date' => 1600008264],
            ['reference' => 'e', 'test_date' => null],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereYear('test_date', 2021)->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['a', 'b', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereYear('test_date', '<', 2021)->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['d'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_where_time()
    {
        $items = collect([
            ['reference' => 'a', 'test_date' => 1637008264],
            ['reference' => 'b', 'test_date' => '2021-11-14 09:00:00'],
            ['reference' => 'c', 'test_date' => '2021-11-15'],
            ['reference' => 'd', 'test_date' => 1600008264],
            ['reference' => 'e', 'test_date' => null],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereTime('test_date', '09:00')->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['b'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereTime('test_date', '>', '09:00')->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'd'], $results->map->reference->all());
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
