<?php

namespace Tests;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class BuilderTest extends TestCase
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
}
