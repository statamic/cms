<?php

namespace Tests\Search;

use Illuminate\Support\Carbon;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Search\Result;
use Statamic\Contracts\Search\Result as SearchResult;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Index;
use Statamic\Search\ProvidesSearchables;
use Statamic\Search\QueryBuilder;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    #[Test]
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

    #[Test]
    public function it_can_get_results_with_data()
    {
        // Using actual searchable providers here simply because calling
        // static methods on mocks is a pain. We override the entries
        // and users providers in the container with the mocks.

        $items = collect([
            ['reference' => 'entry::a', 'search_score' => 2],
            ['reference' => 'user::b', 'search_score' => 1],
            ['reference' => 'entry::c', 'search_score' => 3],
        ]);

        $resultA = Mockery::mock(SearchResult::class);
        $resultA->shouldReceive('setIndex')->once()->andReturnSelf();
        $resultA->shouldReceive('setRawResult')->with(['reference' => 'entry::a', 'search_score' => 2])->once()->andReturnSelf();
        $resultA->shouldReceive('setScore')->with(2)->once()->andReturnSelf();
        $resultA->shouldReceive('getScore')->andReturn(2)->once();
        $resultA->shouldReceive('getReference')->andReturn('entry::a')->once();
        $resultB = Mockery::mock(SearchResult::class);
        $resultB->shouldReceive('setIndex')->once()->andReturnSelf();
        $resultB->shouldReceive('setRawResult')->with(['reference' => 'user::b', 'search_score' => 1])->once()->andReturnSelf();
        $resultB->shouldReceive('setScore')->with(1)->once()->andReturnSelf();
        $resultB->shouldReceive('getScore')->andReturn(1)->once();
        $resultB->shouldReceive('getReference')->andReturn('user::b')->once();
        $resultC = Mockery::mock(SearchResult::class);
        $resultC->shouldReceive('setIndex')->once()->andReturnSelf();
        $resultC->shouldReceive('setRawResult')->with(['reference' => 'entry::c', 'search_score' => 3])->once()->andReturnSelf();
        $resultC->shouldReceive('setScore')->with(3)->once()->andReturnSelf();
        $resultC->shouldReceive('getScore')->andReturn(3)->once();
        $resultC->shouldReceive('getReference')->andReturn('entry::c')->once();

        $a = Mockery::mock(Searchable::class);
        $a->shouldReceive('toSearchResult')->andReturn($resultA);
        $b = Mockery::mock(Searchable::class);
        $b->shouldReceive('toSearchResult')->andReturn($resultB);
        $c = Mockery::mock(Searchable::class);
        $c->shouldReceive('toSearchResult')->andReturn($resultC);

        $foo = Mockery::mock(ProvidesSearchables::class);
        $foo->shouldReceive('find')->with(['a', 'c'])->andReturn(collect([$c, $a])); // return it in the wrong order to make sure it gets ordered by score
        $this->app->instance(\Statamic\Search\Searchables\Entries::class, $foo);

        $bar = Mockery::mock(ProvidesSearchables::class);
        $bar->shouldReceive('find')->with(['b'])->andReturn(collect([$b]));
        $this->app->instance(\Statamic\Search\Searchables\Users::class, $bar);

        $results = (new FakeQueryBuilder($items))->get();

        $this->assertCount(3, $results);
        $this->assertEveryItemIsInstanceOf(Result::class, $results);
        $this->assertEquals([$resultC, $resultA, $resultB], $results->all());
    }

    #[Test]
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

    #[Test]
    public function results_are_found_using_or_where()
    {
        $this->markTestSkipped();
    }

    #[Test]
    public function results_are_found_using_or_where_in()
    {
        $this->markTestSkipped();
    }

    #[Test]
    public function results_are_found_using_where_date()
    {
        $items = $this->createWhereDateTestItems();

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

    #[Test]
    public function results_are_found_using_where_month()
    {
        $items = $this->createWhereDateTestItems();

        $results = (new FakeQueryBuilder($items))->withoutData()->whereMonth('test_date', 11)->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['a', 'b', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereMonth('test_date', '<', 11)->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['d'], $results->map->reference->all());
    }

    #[Test]
    public function results_are_found_using_where_day()
    {
        $items = $this->createWhereDateTestItems();

        $results = (new FakeQueryBuilder($items))->withoutData()->whereDay('test_date', 15)->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereDay('test_date', '<', 15)->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['b', 'd'], $results->map->reference->all());
    }

    #[Test]
    public function results_are_found_using_where_year()
    {
        $items = $this->createWhereDateTestItems();

        $results = (new FakeQueryBuilder($items))->withoutData()->whereYear('test_date', 2021)->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['a', 'b', 'c'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereYear('test_date', '<', 2021)->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['d'], $results->map->reference->all());
    }

    #[Test]
    public function results_are_found_using_where_time()
    {
        $items = $this->createWhereDateTestItems();

        $results = (new FakeQueryBuilder($items))->withoutData()->whereTime('test_date', '09:00')->get();

        $this->assertCount(1, $results);
        $this->assertEquals(['b'], $results->map->reference->all());

        $results = (new FakeQueryBuilder($items))->withoutData()->whereTime('test_date', '>', '09:00')->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'd'], $results->map->reference->all());
    }

    private function createWhereDateTestItems()
    {
        return collect([
            ['reference' => 'a', 'test_date' => Carbon::parse('2021-11-15 20:31:04')],
            ['reference' => 'b', 'test_date' => Carbon::parse('2021-11-14 09:00:00')],
            ['reference' => 'c', 'test_date' => Carbon::parse('2021-11-15 00:00:00')],
            ['reference' => 'd', 'test_date' => Carbon::parse('2020-09-13 14:44:24')],
            ['reference' => 'e', 'test_date' => null],
        ]);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function results_are_found_using_or_where_null()
    {
        $this->markTestSkipped();
    }

    #[Test]
    public function results_are_found_using_or_where_not_null()
    {
        $this->markTestSkipped();
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function results_are_found_using_or_where_between()
    {
        $this->markTestSkipped();
    }

    #[Test]
    public function results_are_found_using_or_where_not_between()
    {
        $this->markTestSkipped();
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function results_are_found_using_where_json_contains()
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

        $results = (new FakeQueryBuilder($items))->withoutData()->whereJsonContains('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(2, $results);
        $this->assertEquals(['a', 'c'], $results->map->reference->all());
    }

    #[Test]
    public function results_are_found_using_where_json_doesnt_contain()
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

        $results = (new FakeQueryBuilder($items))->withoutData()->whereJsonDoesntContain('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['b', 'd', 'e'], $results->map->reference->all());
    }

    #[Test]
    public function results_are_found_using_or_where_json_contains()
    {
        $items = collect([
            ['reference' => 'a', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']],
            ['reference' => 'b', 'test_taxonomy' => ['taxonomy-3']],
            ['reference' => 'c', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']],
            ['reference' => 'd', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']],
            ['reference' => 'e', 'test_taxonomy' => ['taxonomy-5']],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonContains('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['a', 'c', 'e'], $results->map->reference->all());
    }

    #[Test]
    public function results_are_found_using_or_where_json_doesnt_contain()
    {
        $items = collect([
            ['reference' => 'a', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']],
            ['reference' => 'b', 'test_taxonomy' => ['taxonomy-3']],
            ['reference' => 'c', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']],
            ['reference' => 'd', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']],
            ['reference' => 'e', 'test_taxonomy' => ['taxonomy-5']],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonDoesntContain('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(4, $results);
        $this->assertEquals(['a', 'c', 'b', 'd'], $results->map->reference->all());
    }

    #[Test]
    public function results_are_found_using_where_json_length()
    {
        $items = collect([
            ['reference' => 'a', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']],
            ['reference' => 'b', 'test_taxonomy' => ['taxonomy-3']],
            ['reference' => 'c', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']],
            ['reference' => 'd', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4', 'taxonomy-5']],
            ['reference' => 'e', 'test_taxonomy' => ['taxonomy-5']],
        ]);

        $results = (new FakeQueryBuilder($items))->withoutData()->whereJsonLength('test_taxonomy', 1)->orWhereJsonLength('test_taxonomy', 3)->get();

        $this->assertCount(3, $results);
        $this->assertEquals(['b', 'e', 'd'], $results->map->reference->all());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function results_are_found_using_offset()
    {
        $items = collect([
            ['reference' => 'a'],
            ['reference' => 'b'],
            ['reference' => 'c'],
            ['reference' => 'd'],
        ]);

        $query = (new FakeQueryBuilder($items))->withoutData();

        $this->assertEquals(['a', 'b', 'c', 'd'], $query->get()->map->reference->all());

        $this->assertEquals(['b', 'c', 'd'], $query->offset(1)->get()->map->reference->all());
    }

    #[Test]
    public function values_can_be_plucked()
    {
        $items = collect([
            ['reference' => 'a', 'title' => 'Frodo', 'type' => 'a'],
            ['reference' => 'b', 'title' => 'Gandalf', 'type' => 'a'],
            ['reference' => 'c', 'title' => 'Frodo\'s Precious', 'type' => 'b'],
            ['reference' => 'd', 'title' => 'Smeagol\'s Precious', 'type' => 'b'],
        ]);

        $query = (new FakeQueryBuilder($items))->withoutData();

        $this->assertEquals([
            'a' => 'Frodo',
            'b' => 'Gandalf',
            'c' => 'Frodo\'s Precious',
            'd' => 'Smeagol\'s Precious',
        ], $query->pluck('title', 'reference')->all());

        $this->assertEquals([
            'Frodo',
            'Gandalf',
            'Frodo\'s Precious',
            'Smeagol\'s Precious',
        ], $query->pluck('title')->all());

        // Assert only queried values are plucked.
        $this->assertSame([
            'Frodo\'s Precious',
            'Smeagol\'s Precious',
        ], $query->where('type', 'b')->pluck('title')->all());
    }
}

class FakeQueryBuilder extends QueryBuilder
{
    protected $results;

    public function __construct($results)
    {
        $this->results = $results;
        parent::__construct(Mockery::mock(Index::class));
    }

    public function getSearchResults($query)
    {
        return $this->results;
    }
}
