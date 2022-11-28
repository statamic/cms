<?php

namespace Tests\Actions;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Actions\DuplicateEntry;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DuplicateEntryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_duplicates_an_entry()
    {
        Collection::make('test')->save();
        EntryFactory::id('alfa-id')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        EntryFactory::id('bravo-id')->collection('test')->slug('bravo')->data(['title' => 'Bravo'])->create();
        EntryFactory::id('charlie-id')->collection('test')->slug('charlie')->data(['title' => 'Charlie'])->create();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa']],
            ['slug' => 'bravo', 'published' => true, 'data' => ['title' => 'Bravo']],
            ['slug' => 'charlie', 'published' => true, 'data' => ['title' => 'Charlie']],
        ], $this->entryData());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
            Entry::find('charlie-id'),
        ]), collect());

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa']],
            ['slug' => 'bravo', 'published' => true, 'data' => ['title' => 'Bravo']],
            ['slug' => 'charlie', 'published' => true, 'data' => ['title' => 'Charlie']],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id']],
            ['slug' => 'charlie-1', 'published' => false, 'data' => ['title' => 'Charlie (Duplicated)', 'duplicated_from' => 'charlie-id']],
        ], $this->entryData());
    }

    /** @test */
    public function it_increments_the_number_if_duplicate_already_exists()
    {
        Collection::make('test')->save();
        EntryFactory::id('alfa-id')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        EntryFactory::id('alfa-duped-id')->collection('test')->slug('alfa-1')->data(['title' => 'Alfa (Duplicated)'])->create();

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
        ]), collect());

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa']],
            ['slug' => 'alfa-1', 'published' => true, 'data' => ['title' => 'Alfa (Duplicated)']],
            ['slug' => 'alfa-2', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated) (2)', 'duplicated_from' => 'alfa-id']],
        ], $this->entryData());
    }

    private function entryData()
    {
        return Entry::all()->map(fn ($entry) => [
            'slug' => $entry->slug(),
            'published' => $entry->published(),
            'data' => $entry->data()->all(),
        ])->all();
    }
}
