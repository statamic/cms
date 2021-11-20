<?php

namespace Tests\Data\Entries;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
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
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_date' => 1637008264])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_date' => '2021-11-14 09:00:00'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_date' => '2021-11-15'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_date' => 1627008264])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_date' => null])->create();

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
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_date' => 1637008264])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_date' => '2021-11-14 09:00:00'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_date' => '2021-11-15'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_date' => 1627008264])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_date' => null])->create();

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
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_date' => 1637008264])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_date' => '2021-11-14 09:00:00'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_date' => '2021-11-15'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_date' => 1627008264])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_date' => null])->create();

        $entries = Entry::query()->whereDay('test_date', 15)->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 3'], $entries->map->title->all());

        $entries = Entry::query()->whereDay('test_date', '<', 15)->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 2'], $entries->map->title->all());
    }

    /** @test **/
    public function entries_are_found_using_where_year()
    {
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_date' => 1637008264])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_date' => '2021-11-14 09:00:00'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_date' => '2021-11-15'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_date' => 1600008264])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_date' => null])->create();

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
        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'test_date' => 1637008264])->create();
        EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'test_date' => '2021-11-14 09:00:00'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'test_date' => '2021-11-15'])->create();
        EntryFactory::id('4')->slug('post-4')->collection('posts')->data(['title' => 'Post 4', 'test_date' => 1600008264])->create();
        EntryFactory::id('5')->slug('post-5')->collection('posts')->data(['title' => 'Post 5', 'test_date' => null])->create();

        $entries = Entry::query()->whereTime('test_date', '09:00')->get();

        $this->assertCount(1, $entries);
        $this->assertEquals(['Post 2'], $entries->map->title->all());

        $entries = Entry::query()->whereTime('test_date', '>', '09:00')->get();

        $this->assertCount(2, $entries);
        $this->assertEquals(['Post 1', 'Post 4'], $entries->map->title->all());
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
}
