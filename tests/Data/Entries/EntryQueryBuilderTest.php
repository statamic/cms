<?php

namespace Tests\Data\Entries;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function createDummyCollectionAndEntries()
    {
        Collection::make('posts')->save();

        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'author' => 'John Doe'])->create();
        $entry = EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'author' => 'John Doe'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'author' => 'John Doe'])->create();

        return $entry;
    }

    /** @test **/
    public function entry_is_found_within_all_created_entries_using_entry_facade_with_find_method()
    {
        $searchedEntry = $this->createDummyCollectionAndEntries();
        $retrievedEntry = Entry::query()->find($searchedEntry->id());

        $this->assertSame($searchedEntry, $retrievedEntry);
    }

    /** @test **/
    public function entry_is_found_within_all_created_entries_and_select_query_columns_are_set_using_entry_facade_with_find_method_with_columns_param()
    {
        $searchedEntry = $this->createDummyCollectionAndEntries();
        $columns = ['title'];
        $retrievedEntry = Entry::query()->find($searchedEntry->id(), $columns);

        $this->assertSame($searchedEntry, $retrievedEntry);
        $this->assertSame($retrievedEntry->selectedQueryColumns(), $columns);
    }

    /** @test **/
    public function entries_are_found_using_or_where()
    {
        $this->createDummyCollectionAndEntries();

        $entries = Entry::query()->where('title', 'Post 1')->orWhere('title', 'Post 3')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_or_where_in()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();

        $entries = Entry::query()->whereIn('title', ['Post 1', 'Post 2'])->orWhereIn('title', ['Post 1', 'Post 4', 'Post 5'])->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 4', 'Post 5'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_or_where_not_in()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();

        $entries = Entry::query()->whereNotIn('title', ['Post 1', 'Post 2'])->orWhereNotIn('title', ['Post 1', 'Post 5'])->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 3', 'Post 4'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_date()
    {
        $this->createWhereDateTestEntries();

        $entries = Entry::query()->whereDate('test_date', '2021-11-15')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->whereDate('test_date', 1637000264)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->whereDate('test_date', '>=', '2021-11-15')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_month()
    {
        $this->createWhereDateTestEntries();

        $entries = Entry::query()->whereMonth('test_date', 11)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->whereMonth('test_date', '<', 11)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 4'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_day()
    {
        $this->createWhereDateTestEntries();

        $entries = Entry::query()->whereDay('test_date', 15)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->whereDay('test_date', '<', 15)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 2', 'Post 4'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_year()
    {
        $this->createWhereDateTestEntries();

        $entries = Entry::query()->whereYear('test_date', 2021)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->whereYear('test_date', '<', 2021)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 4'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_time()
    {
        $this->createWhereDateTestEntries();

        $entries = Entry::query()->whereTime('test_date', '09:00')->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 2'], $entries->map->title->all());

        $entries = Entry::query()->whereTime('test_date', '>', '09:00')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 4'], $entries->map->title->all());
    }

    private function createWhereDateTestEntries()
    {
        $blueprint = Blueprint::makeFromFields(['test_date' => ['type' => 'date', 'time_enabled' => true]]);
        Blueprint::shouldReceive('in')->with('collections/posts')->andReturn(collect(['posts' => $blueprint]));

        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_date' => '2021-11-15 20:31:04'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_date' => '2021-11-14 09:00:00'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_date' => '2021-11-15 00:00:00'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_date' => '2020-09-13 14:44:24'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_date' => null])->create();
    }

    public function entries_are_found_using_where_null()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'text' => 'Text 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'text' => 'Text 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'text' => 'Text 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();

        $entries = Entry::query()->whereNull('text')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 3', 'Post 5'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_not_null()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'text' => 'Text 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'text' => 'Text 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'text' => 'Text 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();

        $entries = Entry::query()->whereNotNull('text')->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 4'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_or_where_null()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'text' => 'Text 1', 'content' => 'Content 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'text' => 'Text 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'content' => 'Content 1'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'text' => 'Text 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();

        $entries = Entry::query()->whereNull('text')->orWhereNull('content')->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['Post 3', 'Post 5', 'Post 2', 'Post 4'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_or_where_not_null()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'text' => 'Text 1', 'content' => 'Content 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'text' => 'Text 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'content' => 'Content 1'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'text' => 'Text 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();

        $entries = Entry::query()->whereNotNull('content')->orWhereNotNull('text')->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['Post 1', 'Post 3', 'Post 2', 'Post 4'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_column()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'other_title' => 'Not Post 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'other_title' => 'Not Post 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'other_title' => 'Post 3'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'other_title' => 'Post 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'other_title' => 'Not Post 5'])->create();

        $entries = Entry::query()->whereColumn('title', 'other_title')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 3', 'Post 4'], $entries->map->title->all());

        $entries = Entry::query()->whereColumn('title', '!=', 'other_title')->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 5'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_nested_where()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();
        EntryFactory::id('6')->slug('post-6')->collection('posts')->data(['title' => 'Post 6'])->create();

        $entries = Entry::query()
            ->where(function ($query) {
                $query->where('title', 'Post 1');
            })
            ->orWhere(function ($query) {
                $query->where('title', 'Post 3')->orWhere('title', 'Post 4');
            })
            ->orWhere('title', 'Post 6')
            ->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['1', '3', '4', '6'], $entries->map->id()->all());
    }

    /** @test **/
    public function entries_are_found_using_nested_where_in()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();
        EntryFactory::id('6')->slug('post-6')->collection('posts')->data(['title' => 'Post 6'])->create();
        EntryFactory::id('7')->slug('post-7')->collection('posts')->data(['title' => 'Post 7'])->create();
        EntryFactory::id('8')->slug('post-8')->collection('posts')->data(['title' => 'Post 8'])->create();
        EntryFactory::id('9')->slug('post-9')->collection('posts')->data(['title' => 'Post 9'])->create();

        $entries = Entry::query()
            ->where(function ($query) {
                $query->whereIn('title', ['Post 1', 'Post 2']);
            })
            ->orWhere(function ($query) {
                $query->where('title', 'Post 4')->orWhereIn('title', ['Post 6', 'Post 7']);
            })
            ->orWhere('title', 'Post 9')
            ->get();

        $this->assertCount(6, $entries);
        $this->assertEquals(['1', '2', '4', '6', '7', '9'], $entries->map->id()->all());
    }

    /** @test **/
    public function entries_are_found_using_where_between()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'number_field' => 8])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'number_field' => 9])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'number_field' => 10])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'number_field' => 11])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'number_field' => 12])->create();

        $entries = Entry::query()->whereBetween('number_field', [9, 11])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 2', 'Post 3', 'Post 4'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_not_between()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'number_field' => 8])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'number_field' => 9])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'number_field' => 10])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'number_field' => 11])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'number_field' => 12])->create();

        $entries = Entry::query()->whereNotBetween('number_field', [9, 11])->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 5'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_or_where_between()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'number_field' => 8])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'number_field' => 9])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'number_field' => 10])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'number_field' => 11])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'number_field' => 12])->create();

        $entries = Entry::query()->whereBetween('number_field', [9, 10])->orWhereBetween('number_field', [11, 12])->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['Post 2', 'Post 3', 'Post 4', 'Post 5'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_or_where_not_between()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'number_field' => 8])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'number_field' => 9])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'number_field' => 10])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'number_field' => 11])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'number_field' => 12])->create();

        $entries = Entry::query()->where('slug', 'post-5')->orWhereNotBetween('number_field', [10, 12])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 5', 'Post 1', 'Post 2'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_array_of_wheres()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'content' => 'Test'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'content' => 'Test two'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'content' => 'Test'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'content' => 'Test two'])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'content' => 'Test'])->create();
        EntryFactory::id('6')->slug('post-6')->collection('posts')->data(['title' => 'Post 6', 'content' => 'Test two'])->create();

        $entries = Entry::query()
            ->where([
                'content' => 'Test',
                ['title', '<>', 'Post 1'],
            ])
            ->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['3', '5'], $entries->map->id()->all());
    }

    /** @test **/
    public function entries_are_found_using_where_with_json_value()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'content' => ['value' => 1]])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'content' => ['value' => 2]])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'content' => ['value' => 3]])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'content' => ['value' => 2]])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'content' => ['value' => 1]])->create();
        // the following two entries use scalars for the content field to test that they get successfully ignored.
        EntryFactory::id('6')->slug('post-6')->collection('posts')->data(['title' => 'Post 6', 'content' => 'string'])->create();
        EntryFactory::id('7')->slug('post-7')->collection('posts')->data(['title' => 'Post 7', 'content' => 123])->create();

        $entries = Entry::query()->where('content->value', 1)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 5'], $entries->map->title->all());

        $entries = Entry::query()->where('content->value', '<>', 1)->get();

        $this->assertCount(5, $entries);
        $this->assertEquals(['Post 2', 'Post 3', 'Post 4', 'Post 6', 'Post 7'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_when()
    {
        $this->createDummyCollectionAndEntries();

        $entries = Entry::query()->when(true, function ($query) {
            $query->where('title', 'Post 1');
        })->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 1'], $entries->map->title->all());

        $entries = Entry::query()->when(false, function ($query) {
            $query->where('title', 'Post 1');
        })->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_unless()
    {
        $this->createDummyCollectionAndEntries();

        $entries = Entry::query()->unless(true, function ($query) {
            $query->where('title', 'Post 1');
        })->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->unless(false, function ($query) {
            $query->where('title', 'Post 1');
        })->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 1'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_tap()
    {
        $this->createDummyCollectionAndEntries();

        $entries = Entry::query()->tap(function ($query) {
            $query->where('title', 'Post 1');
        })->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 1'], $entries->map->title->all());
    }

    /** @test */
    public function it_substitutes_entries_by_id()
    {
        Collection::make('posts')->routes('/posts/{slug}')->save();
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->create();

        $substitute = EntryFactory::id('2')->slug('replaced-post-2')->collection('posts')->data(['title' => 'Replaced Post 2'])->make();

        $found = Entry::query()->where('id', 2)->first();
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substitute);

        Entry::substitute($substitute);

        $found = Entry::query()->where('id', 2)->first();
        $this->assertNotNull($found);
        $this->assertSame($found, $substitute);
    }

    /** @test */
    public function it_substitutes_entries_by_uri()
    {
        Collection::make('posts')->routes('/posts/{slug}')->save();
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->create();

        $substitute = EntryFactory::id('2')->slug('replaced-post-2')->collection('posts')->data(['title' => 'Replaced Post 2'])->make();

        $found = Entry::findByUri('/posts/post-2');
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substitute);

        $this->assertNull(Entry::findByUri('/posts/replaced-post-2'));

        Entry::substitute($substitute);

        $found = Entry::findByUri('/posts/replaced-post-2');
        $this->assertNotNull($found);
        $this->assertSame($found, $substitute);
    }

    /** @test */
    public function it_substitutes_entries_by_uri_and_site()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]]);

        Collection::make('posts')->routes('/posts/{slug}')->sites(['en', 'fr'])->save();
        EntryFactory::id('en-1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->locale('en')->create();
        EntryFactory::id('en-2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->locale('en')->create();
        EntryFactory::id('en-3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->locale('en')->create();
        EntryFactory::id('fr-1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->locale('fr')->create();
        EntryFactory::id('fr-2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->locale('fr')->create();
        EntryFactory::id('fr-3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3'])->locale('fr')->create();

        $substituteEn = EntryFactory::id('en-2')->slug('replaced-post-2')->collection('posts')->data(['title' => 'Replaced Post 2'])->locale('en')->make();
        $substituteFr = EntryFactory::id('fr-2')->slug('replaced-post-2')->collection('posts')->data(['title' => 'Replaced Post 2'])->locale('fr')->make();

        $found = Entry::findByUri('/posts/post-2');
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substituteEn);

        $found = Entry::findByUri('/posts/post-2', 'en');
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substituteEn);

        $found = Entry::findByUri('/posts/post-2', 'fr');
        $this->assertNotNull($found);
        $this->assertNotSame($found, $substituteFr);

        $this->assertNull(Entry::findByUri('/posts/replaced-post-2'));
        $this->assertNull(Entry::findByUri('/posts/replaced-post-2', 'en'));
        $this->assertNull(Entry::findByUri('/posts/replaced-post-2', 'fr'));

        Entry::substitute($substituteEn);
        Entry::substitute($substituteFr);

        $found = Entry::findByUri('/posts/replaced-post-2');
        $this->assertNotNull($found);
        $this->assertSame($found, $substituteEn);

        $found = Entry::findByUri('/posts/replaced-post-2', 'en');
        $this->assertNotNull($found);
        $this->assertSame($found, $substituteEn);

        $found = Entry::findByUri('/posts/replaced-post-2', 'fr');
        $this->assertNotNull($found);
        $this->assertSame($found, $substituteFr);
    }
}
