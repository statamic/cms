<?php

namespace Tests\Data\Entries;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Query\Exceptions\MultipleRecordsFoundException;
use Statamic\Query\Exceptions\RecordsNotFoundException;
use Statamic\Query\Scopes\Scope;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class EntryQueryBuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function createDummyCollectionAndEntries()
    {
        Collection::make('posts')->save();

        EntryFactory::id('id-1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'author' => 'John Doe'])->create();
        $entry = EntryFactory::id('id-2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'author' => 'John Doe'])->create();
        EntryFactory::id('id-3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'author' => 'John Doe'])->create();

        return $entry;
    }

    #[Test]
    public function entry_is_found_within_all_created_entries_using_entry_facade_with_find_method()
    {
        $searchedEntry = $this->createDummyCollectionAndEntries();
        $retrievedEntry = Entry::query()->find($searchedEntry->id());

        $this->assertSame($searchedEntry, $retrievedEntry);
    }

    #[Test]
    public function entry_is_found_within_all_created_entries_and_select_query_columns_are_set_using_entry_facade_with_find_method_with_columns_param()
    {
        $searchedEntry = $this->createDummyCollectionAndEntries();
        $columns = ['title'];
        $retrievedEntry = Entry::query()->find($searchedEntry->id(), $columns);

        $this->assertSame($searchedEntry, $retrievedEntry);
        $this->assertSame($retrievedEntry->selectedQueryColumns(), $columns);
    }

    #[Test]
    public function entries_are_found_using_or_where()
    {
        $this->createDummyCollectionAndEntries();

        $entries = Entry::query()->where('title', 'Post 1')->orWhere('title', 'Post 3')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function entries_are_found_using_where_month()
    {
        $this->createWhereDateTestEntries();

        $entries = Entry::query()->whereMonth('test_date', 11)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->whereMonth('test_date', 9)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 4'], $entries->map->title->all());

        $entries = Entry::query()->whereMonth('test_date', '<', 11)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 4'], $entries->map->title->all());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function entries_are_found_using_where_time()
    {
        $this->createWhereDateTestEntries();

        $entries = Entry::query()->whereTime('test_date', '09:00')->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 2'], $entries->map->title->all());

        $entries = Entry::query()->whereTime('test_date', '>', '09:00')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 4'], $entries->map->title->all());

        // if we send full dates it should only consider the time part
        $entries = Entry::query()->whereTime('test_date', '2021-11-13 09:00')->get();
        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 2'], $entries->map->title->all());

        $entries = Entry::query()->whereTime('test_date', Carbon::createFromFormat('Y-m-d H:i', '2021-11-13 09:00'))->get();
        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 2'], $entries->map->title->all());
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function entries_are_found_using_where_json_contains()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_taxonomy' => ['taxonomy-3']])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_taxonomy' => ['taxonomy-5']])->create();

        $entries = Entry::query()->whereJsonContains('test_taxonomy', ['taxonomy-1', 'taxonomy-5'])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 3', 'Post 5'], $entries->map->title->all());

        $entries = Entry::query()->whereJsonContains('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());
    }

    #[Test]
    public function entries_are_found_using_where_json_doesnt_contain()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_taxonomy' => ['taxonomy-3']])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_taxonomy' => ['taxonomy-5']])->create();

        $entries = Entry::query()->whereJsonDoesntContain('test_taxonomy', ['taxonomy-1'])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 2', 'Post 4', 'Post 5'], $entries->map->title->all());

        $entries = Entry::query()->whereJsonDoesntContain('test_taxonomy', 'taxonomy-1')->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 2', 'Post 4', 'Post 5'], $entries->map->title->all());
    }

    #[Test]
    public function entries_are_found_using_or_where_json_contains()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_taxonomy' => ['taxonomy-3']])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_taxonomy' => ['taxonomy-5']])->create();

        $entries = Entry::query()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonContains('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 3', 'Post 5'], $entries->map->title->all());
    }

    #[Test]
    public function entries_are_found_using_or_where_json_doesnt_contain()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_taxonomy' => ['taxonomy-3']])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4']])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_taxonomy' => ['taxonomy-5']])->create();

        $entries = Entry::query()->whereJsonContains('test_taxonomy', ['taxonomy-1'])->orWhereJsonDoesntContain('test_taxonomy', ['taxonomy-5'])->get();

        $this->assertCount(4, $entries);
        $this->assertEquals(['Post 1', 'Post 3', 'Post 2', 'Post 4'], $entries->map->title->all());
    }

    #[Test]
    public function entries_are_found_using_where_json_length()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-2']])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_taxonomy' => ['taxonomy-3']])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_taxonomy' => ['taxonomy-1', 'taxonomy-3']])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_taxonomy' => ['taxonomy-3', 'taxonomy-4', 'taxonomy-5']])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_taxonomy' => ['taxonomy-5']])->create();

        $entries = Entry::query()->whereJsonLength('test_taxonomy', 1)->orWhereJsonLength('test_taxonomy', 3)->get();

        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 2', 'Post 5', 'Post 4'], $entries->map->title->all());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function entries_are_found_using_tap()
    {
        $this->createDummyCollectionAndEntries();

        $entries = Entry::query()->tap(function ($query) {
            $query->where('title', 'Post 1');
        })->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 1'], $entries->map->title->all());
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_substitutes_entries_by_uri_and_site()
    {
        $this->setSites([
            'en' => ['url' => 'http://localhost/', 'locale' => 'en'],
            'fr' => ['url' => 'http://localhost/fr/', 'locale' => 'fr'],
        ]);

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

    #[Test]
    public function entries_are_found_using_scopes()
    {
        CustomScope::register();
        Entry::allowQueryScope(CustomScope::class);
        Entry::allowQueryScope(CustomScope::class, 'whereCustom');

        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1'])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2'])->create();

        $this->assertCount(1, Entry::query()->customScope(['title' => 'Post 1'])->get());
        $this->assertCount(1, Entry::query()->whereCustom(['title' => 'Post 1'])->get());
    }

    #[Test]
    public function entries_are_found_using_offset()
    {
        $this->createDummyCollectionAndEntries();

        $entries = Entry::query()->get();
        $this->assertCount(3, $entries);
        $this->assertEquals(['Post 1', 'Post 2', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->offset(1)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 2', 'Post 3'], $entries->map->title->all());
    }

    #[Test]
    #[DataProvider('likeProvider')]
    public function entries_are_found_using_like($like, $expected)
    {
        Collection::make('posts')->save();

        collect([
            'on',
            'only',
            'foo',
            'food',
            'boo',
            'foo bar',
            'foo_bar',
            'foodbar',
            'hello world',
            'waterworld',
            'world of warcraft',
            '20%',
            '20% of the time',
            '20 something',
            'Pi is 3.14159',
            'Pi is not 3x14159',
            'Use a [4.x] prefix for PRs',
            '/',
            '/ test',
            'test /',
            'test / test',
        ])->each(function ($val, $i) {
            EntryFactory::id($i)
                ->slug('post-'.$i)
                ->collection('posts')
                ->data(['title' => $val])
                ->create();
        });

        $this->assertEquals($expected, Entry::query()->where('title', 'like', $like)->get()->map->title->all());
    }

    public static function likeProvider()
    {
        return collect([
            'foo' => ['foo'],
            'foo%' => ['foo', 'food', 'foo bar', 'foo_bar', 'foodbar'],
            '%world' => ['hello world', 'waterworld'],
            '%world%' => ['hello world', 'waterworld', 'world of warcraft'],
            '_oo' => ['foo', 'boo'],
            'o_' => ['on'],
            'foo_bar' => ['foo bar', 'foo_bar', 'foodbar'],
            'foo__bar' => [],
            'fo__bar' => ['foo bar', 'foo_bar', 'foodbar'],
            'foo\_bar' => ['foo_bar'],
            '20\%' => ['20%'],
            '20\%%' => ['20%', '20% of the time'],
            '%3.14%' => ['Pi is 3.14159'],
            '%[4%' => ['Use a [4.x] prefix for PRs'],
            '/' => ['/'],
            '%/' => ['/', 'test /'],
            '/%' => ['/', '/ test'],
            '%/%' => ['/', '/ test', 'test /', 'test / test'],
        ])->mapWithKeys(function ($expected, $like) {
            return [$like => [$like, $expected]];
        });
    }

    #[Test]
    public function entries_are_found_using_chunk()
    {
        $this->createDummyCollectionAndEntries();

        $chunks = 0;

        Entry::query()->chunk(2, function ($entries, $page) use (&$chunks) {
            if ($page === 1) {
                $this->assertCount(2, $entries);
                $this->assertEquals(['Post 1', 'Post 2'], $entries->map->title->all());
            } else {
                $this->assertCount(1, $entries);
                $this->assertEquals(['Post 3'], $entries->map->title->all());
            }

            $chunks++;
        });

        $this->assertEquals(2, $chunks);
    }

    #[Test]
    public function entries_are_found_using_chunk_with_limits_where_limit_is_less_than_total()
    {
        $this->createDummyCollectionAndEntries();

        $chunks = 0;

        Entry::query()->limit(2)->chunk(1, function ($entries, $page) use (&$chunks) {
            if ($page === 1) {
                $this->assertCount(1, $entries);
                $this->assertEquals(['Post 1'], $entries->map->title->all());
            } else {
                $this->assertCount(1, $entries);
                $this->assertEquals(['Post 2'], $entries->map->title->all());
            }

            $chunks++;
        });

        $this->assertEquals(2, $chunks);
    }

    #[Test]
    public function entries_are_found_using_chunk_with_limits_where_limit_is_more_than_total()
    {
        $this->createDummyCollectionAndEntries();

        $chunks = 0;

        Entry::query()->limit(10)->chunk(2, function ($entries, $page) use (&$chunks) {
            if ($page === 1) {
                $this->assertCount(2, $entries);
                $this->assertEquals(['Post 1', 'Post 2'], $entries->map->title->all());
            } elseif ($page === 2) {
                $this->assertCount(1, $entries);
                $this->assertEquals(['Post 3'], $entries->map->title->all());
            } else {
                $this->fail('Should have had two pages.');
            }

            $chunks++;
        });

        $this->assertEquals(2, $chunks);
    }

    #[Test]
    public function entries_are_found_using_chunk_with_offset()
    {
        $this->createDummyCollectionAndEntries();

        $chunks = 0;

        Entry::query()->offset(1)->chunk(2, function ($entries, $page) use (&$chunks) {
            if ($page === 1) {
                $this->assertCount(2, $entries);
                $this->assertEquals(['Post 2', 'Post 3'], $entries->map->title->all());
            } else {
                $this->fail('Should only have had one page.');
            }

            $chunks++;
        });

        $this->assertEquals(1, $chunks);
    }

    #[Test]
    public function entries_are_found_using_chunk_with_offset_where_more_than_total()
    {
        $this->createDummyCollectionAndEntries();

        $chunks = 0;

        Entry::query()->offset(3)->chunk(2, function ($entries, $page) use (&$chunks) {
            $chunks++;
        });

        $this->assertEquals(0, $chunks);
    }

    #[Test]
    public function entries_are_found_using_chunk_with_limits_and_offsets()
    {
        $this->createDummyCollectionAndEntries();

        EntryFactory::id('id-4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4'])->create();
        EntryFactory::id('id-5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5'])->create();
        EntryFactory::id('id-6')->slug('post-6')->collection('posts')->data(['title' => 'Post 6'])->create();
        EntryFactory::id('id-7')->slug('post-7')->collection('posts')->data(['title' => 'Post 7'])->create();

        $chunks = 0;

        Entry::query()->orderBy('id', 'asc')->offset(2)->limit(3)->chunk(2, function ($entries, $page) use (&$chunks) {
            if ($page === 1) {
                $this->assertCount(2, $entries);
                $this->assertEquals(['Post 3', 'Post 4'], $entries->map->title->all());
            } elseif ($page === 2) {
                $this->assertCount(1, $entries);
                $this->assertEquals(['Post 5'], $entries->map->title->all());
            } else {
                $this->fail('Should only have had two pages.');
            }

            $chunks++;
        });

        $this->assertEquals(2, $chunks);
    }

    #[Test]
    public function entries_are_found_using_lazy()
    {
        $this->createDummyCollectionAndEntries();

        $entries = Entry::query()->lazy();

        $this->assertInstanceOf(\Illuminate\Support\LazyCollection::class, $entries);
        $this->assertCount(3, $entries);
    }

    #[Test]
    public function entries_can_be_reordered()
    {
        $this->createDummyCollectionAndEntries();

        $this->assertSame(['post-3', 'post-2', 'post-1'], Entry::query()->orderBy('title', 'desc')->get()->map->slug()->all());

        $this->assertSame(['post-1', 'post-2', 'post-3'], Entry::query()->orderBy('title', 'desc')->reorder()->orderBy('asc', 'desc')->get()->map->slug()->all());
    }

    #[Test]
    public function filtering_using_where_status_column_writes_deprecation_log()
    {
        $this->withoutDeprecationHandling();
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Filtering by status is deprecated. Use whereStatus() instead.');

        $this->createDummyCollectionAndEntries();

        Entry::query()->where('collection', 'posts')->where('status', 'published')->get();
    }

    #[Test]
    public function filtering_using_whereIn_status_column_writes_deprecation_log()
    {
        $this->withoutDeprecationHandling();
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Filtering by status is deprecated. Use whereStatus() instead.');

        $this->createDummyCollectionAndEntries();

        Entry::query()->where('collection', 'posts')->whereIn('status', ['published'])->get();
    }

    #[Test]
    public function filtering_by_unexpected_status_throws_exception()
    {
        $this->expectExceptionMessage('Invalid status [foo]');

        Entry::query()->whereStatus('foo')->get();
    }

    #[Test]
    #[DataProvider('filterByStatusProvider')]
    public function it_filters_by_status($status, $expected)
    {
        Collection::make('pages')->dated(false)->save();
        EntryFactory::collection('pages')->id('page')->published(true)->create();
        EntryFactory::collection('pages')->id('page-draft')->published(false)->create();

        Collection::make('blog')->dated(true)->futureDateBehavior('private')->pastDateBehavior('public')->save();
        EntryFactory::collection('blog')->id('blog-future')->published(true)->date(now()->addDay())->create();
        EntryFactory::collection('blog')->id('blog-future-draft')->published(false)->date(now()->addDay())->create();
        EntryFactory::collection('blog')->id('blog-past')->published(true)->date(now()->subDay())->create();
        EntryFactory::collection('blog')->id('blog-past-draft')->published(false)->date(now()->subDay())->create();

        Collection::make('events')->dated(true)->futureDateBehavior('public')->pastDateBehavior('private')->save();
        EntryFactory::collection('events')->id('event-future')->published(true)->date(now()->addDay())->create();
        EntryFactory::collection('events')->id('event-future-draft')->published(false)->date(now()->addDay())->create();
        EntryFactory::collection('events')->id('event-past')->published(true)->date(now()->subDay())->create();
        EntryFactory::collection('events')->id('event-past-draft')->published(false)->date(now()->subDay())->create();

        Collection::make('calendar')->dated(true)->futureDateBehavior('public')->pastDateBehavior('public')->save();
        EntryFactory::collection('calendar')->id('calendar-future')->published(true)->date(now()->addDay())->create();
        EntryFactory::collection('calendar')->id('calendar-future-draft')->published(false)->date(now()->addDay())->create();
        EntryFactory::collection('calendar')->id('calendar-past')->published(true)->date(now()->subDay())->create();
        EntryFactory::collection('calendar')->id('calendar-past-draft')->published(false)->date(now()->subDay())->create();

        // Undated, but with customized date behavior. Nonsensical situation, but it can happen.
        // See https://github.com/statamic/eloquent-driver/issues/288
        Collection::make('undated')->dated(false)->futureDateBehavior('private')->pastDateBehavior('private')->save();
        EntryFactory::collection('undated')->id('undated')->published(true)->create();
        EntryFactory::collection('undated')->id('undated-draft')->published(false)->create();

        $this->assertEquals($expected, Entry::query()->whereStatus($status)->get()->map->id->all());
    }

    public static function filterByStatusProvider()
    {
        return [
            'draft' => ['draft', [
                'page-draft',
                'blog-future-draft',
                'blog-past-draft',
                'event-future-draft',
                'event-past-draft',
                'calendar-future-draft',
                'calendar-past-draft',
                'undated-draft',
            ]],
            'published' => ['published', [
                'page',
                'blog-past',
                'event-future',
                'calendar-future',
                'calendar-past',
                'undated',
            ]],
            'scheduled' => ['scheduled', [
                'blog-future',
            ]],
            'expired' => ['expired', [
                'event-past',
            ]],
        ];
    }

    public function values_can_be_plucked()
    {
        $this->createDummyCollectionAndEntries();
        Entry::find('id-2')->set('type', 'b')->save();
        Entry::find('id-3')->set('type', 'b')->save();
        Collection::make('things')->save();
        EntryFactory::id('id-4')->slug('thing-1')->collection('things')->data(['title' => 'Thing 1', 'type' => 'a'])->create();
        EntryFactory::id('id-5')->slug('thing-2')->collection('things')->data(['title' => 'Thing 2', 'type' => 'b'])->create();

        $this->assertEquals([
            'id-1' => 'post-1',
            'id-2' => 'post-2',
            'id-3' => 'post-3',
            'id-4' => 'thing-1',
            'id-5' => 'thing-2',
        ], Entry::query()->pluck('slug', 'id')->all());

        $this->assertEquals([
            'post-1',
            'post-2',
            'post-3',
            'thing-1',
            'thing-2',
        ], Entry::query()->pluck('slug')->all());

        // Assert only queried values are plucked.
        $this->assertSame([
            'post-2',
            'post-3',
            'thing-2',
        ], Entry::query()->where('type', 'b')->pluck('slug')->all());
    }

    #[Test]
    public function entry_can_be_found_using_first_or_fail()
    {
        Collection::make('posts')->save();
        $entry = EntryFactory::collection('posts')->id('hoff')->slug('david-hasselhoff')->data(['title' => 'David Hasselhoff'])->create();

        $firstOrFail = Entry::query()
            ->where('collection', 'posts')
            ->where('id', 'hoff')
            ->firstOrFail();

        $this->assertSame($entry, $firstOrFail);
    }

    #[Test]
    public function exception_is_thrown_when_entry_does_not_exist_using_first_or_fail()
    {
        $this->expectException(RecordsNotFoundException::class);

        Entry::query()
            ->where('collection', 'posts')
            ->where('id', 'ze-hoff')
            ->firstOrFail();
    }

    #[Test]
    public function entry_can_be_found_using_first_or()
    {
        Collection::make('posts')->save();
        $entry = EntryFactory::collection('posts')->id('hoff')->slug('david-hasselhoff')->data(['title' => 'David Hasselhoff'])->create();

        $firstOrFail = Entry::query()
            ->where('collection', 'posts')
            ->where('id', 'hoff')
            ->firstOr(function () {
                return 'fallback';
            });

        $this->assertSame($entry, $firstOrFail);
    }

    #[Test]
    public function callback_is_called_when_entry_does_not_exist_using_first_or()
    {
        $firstOrFail = Entry::query()
            ->where('collection', 'posts')
            ->where('id', 'hoff')
            ->firstOr(function () {
                return 'fallback';
            });

        $this->assertSame('fallback', $firstOrFail);
    }

    #[Test]
    public function sole_entry_is_returned()
    {
        Collection::make('posts')->save();
        $entry = EntryFactory::collection('posts')->id('hoff')->slug('david-hasselhoff')->data(['title' => 'David Hasselhoff'])->create();

        $sole = Entry::query()
            ->where('collection', 'posts')
            ->where('id', 'hoff')
            ->sole();

        $this->assertSame($entry, $sole);
    }

    #[Test]
    public function exception_is_thrown_by_sole_when_multiple_entries_are_returned_from_query()
    {
        Collection::make('posts')->save();
        EntryFactory::collection('posts')->id('hoff')->slug('david-hasselhoff')->data(['title' => 'David Hasselhoff'])->create();
        EntryFactory::collection('posts')->id('smoff')->slug('joe-hasselsmoff')->data(['title' => 'Joe Hasselsmoff'])->create();

        $this->expectException(MultipleRecordsFoundException::class);

        Entry::query()
            ->where('collection', 'posts')
            ->sole();
    }

    #[Test]
    public function exception_is_thrown_by_sole_when_no_entries_are_returned_from_query()
    {
        $this->expectException(RecordsNotFoundException::class);

        Entry::query()
            ->where('collection', 'posts')
            ->sole();
    }

    #[Test]
    public function exists_returns_true_when_results_are_found()
    {
        $this->createDummyCollectionAndEntries();

        $this->assertTrue(Entry::query()->exists());
    }

    #[Test]
    public function exists_returns_false_when_no_results_are_found()
    {
        $this->assertFalse(Entry::query()->exists());
    }
}

class CustomScope extends Scope
{
    public function apply($query, $params)
    {
        $query->where('title', $params['title']);
    }
}
