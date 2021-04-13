<?php

namespace Tests\Query;

use Carbon\Carbon;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class BuilderTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function createDummyCollectionAndEntries()
    {
        Collection::make('posts')->save();

        EntryFactory::id('1')->slug('post-1')->collection('posts')->data(['title' => 'Post 1', 'author' => 'John Doe', 'publish_date' => '2020-12-21'])->create();
        $entry = EntryFactory::id('2')->slug('post-2')->collection('posts')->data(['title' => 'Post 2', 'author' => 'Jane Smith', 'publish_date' => '2021-01-02'])->create();
        EntryFactory::id('3')->slug('post-3')->collection('posts')->data(['title' => 'Post 3', 'author' => 'John Doe', 'publish_date' => '2020-12-12'])->create();

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

    /** @test */
    public function entries_are_returned_where_author_is_john_doe()
    {
        $this->createDummyCollectionAndEntries();

        $queryResults = Entry::query()->where('author', 'John Doe')->get();

        $this->assertCount(2, $queryResults);

        $this->assertSame('1', $queryResults->first()->id());
        $this->assertSame('3', $queryResults->last()->id());

        $this->assertArrayNotHasKey('2', $queryResults->pluck('id')->toArray());
    }

    /** @test */
    public function entries_are_returned_where_date_is_greater_than_1st_january()
    {
        $this->createDummyCollectionAndEntries();

        $queryResults = Entry::query()->whereDate('publish_date', '>=', Carbon::parse('1st January 2021'))->get();

        $this->assertCount(1, $queryResults);

        $this->assertSame('2', $queryResults->first()->id());

        $this->assertArrayNotHasKey('1', $queryResults->pluck('id')->toArray());
        $this->assertArrayNotHasKey('3', $queryResults->pluck('id')->toArray());
    }

    /** @test */
    public function entries_are_returned_where_in_title()
    {
        $this->createDummyCollectionAndEntries();

        $queryResults = Entry::query()->whereIn('title', ['Post 1', 'Post 2'])->get();

        $this->assertCount(2, $queryResults);

        $this->assertSame('1', $queryResults->first()->id());
        $this->assertSame('2', $queryResults->last()->id());

        $this->assertArrayNotHasKey('3', $queryResults->pluck('id')->toArray());
    }
}
