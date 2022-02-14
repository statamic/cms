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

    /** @test **/
    public function results_are_found_using_multiple_wheres()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo'],
            ['reference' => 'b', 'title' => 'Gandalf'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->where('title', 'like', '%Frodo%')->where('reference', 'a')->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['a'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_array_of_wheres()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo'],
            ['reference' => 'b', 'title' => 'Gandalf'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious'],
            ['reference' => 'e', 'title' => 'Gandalf'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()
            ->where([
                'title' => 'Gandalf',
                ['reference', '<>', 'b'],
            ])
            ->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['e'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_where_with_json_value()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo', 'content' => ['value' => 1]],
            ['reference' => 'b', 'title' => 'Gandalf', 'content' => ['value' => 2]],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious', 'content' => ['value' => 3]],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious', 'content' => ['value' => 1]],
            // the following two results use scalars for the content field to test that they get successfully ignored.
            ['reference' => 'e', 'title' => 'Arwen', 'content' => 'string'],
            ['reference' => 'f', 'title' => 'Bilbo', 'content' => 123],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()
            ->where('content->value', 1)
            ->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'd'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()
            ->where('content->value', '<>', 1)
            ->get();

        $this->assertCount(4, $results);
        $this->assertEquals(['b', 'c', 'e', 'f'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_where_column()
    {
        $items = collect([
            ['reference' => 'a', 'foo' => 'Post 1', 'other_foo' => 'Not Post 1'],
            ['reference' => 'b', 'foo' => 'Post 2', 'other_foo' => 'Not Post 2'],
            ['reference' => 'c', 'foo' => 'Post 3', 'other_foo' => 'Post 3'],
            ['reference' => 'd', 'foo' => 'Post 4', 'other_foo' => 'Post 4'],
            ['reference' => 'e', 'foo' => 'Post 5', 'other_foo' => 'Not Post 5'],
            ['reference' => 'f', 'foo' => 'Post 6', 'other_foo' => 'Not Post 6'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()
            ->whereColumn('foo', 'other_foo')
            ->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['Post 3', 'Post 4'], $results->map->foo->all());

        $results = (new FakeQueryBuilder($items))->withoutData()
            ->whereColumn('foo', '!=', 'other_foo')
            ->get();

        $this->assertCount(4, $results);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 5', 'Post 6'], $results->map->foo->all());
    }

    /** @test **/
    public function results_are_found_using_when()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo'],
            ['reference' => 'b', 'title' => 'Gandalf'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->when(true, function ($query) {
            $query->where('title', 'like', '%Frodo%');
        })->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->when(false, function ($query) {
            $query->where('title', 'like', '%Frodo%');
        })->get();

        $this->assertCount(4, $results);
        $this->assertEquals(['a', 'b', 'c', 'd'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_unless()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo'],
            ['reference' => 'b', 'title' => 'Gandalf'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->unless(true, function ($query) {
            $query->where('title', 'like', '%Frodo%');
        })->get();

        $this->assertCount(4, $results);
        $this->assertEquals(['a', 'b', 'c', 'd'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->unless(false, function ($query) {
            $query->where('title', 'like', '%Frodo%');
        })->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());
    }

    /** @test **/
    public function results_are_found_using_tap()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo'],
            ['reference' => 'b', 'title' => 'Gandalf'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious'],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->tap(function ($query) {
            $query->where('title', 'like', '%Frodo%');
        })->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());
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
