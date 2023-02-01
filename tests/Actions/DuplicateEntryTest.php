<?php

namespace Tests\Actions;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Actions\DuplicateEntry;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DuplicateEntryTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use FakesRoles;

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

    /** @test */
    public function user_with_create_permission_is_authorized()
    {
        $this->setTestRoles([
            'access' => ['create test entries'],
            'noaccess' => [],
        ]);

        Collection::make('test')->save();
        $userWithPermission = tap(User::make()->assignRole('access'))->save();
        $userWithoutPermission = tap(User::make()->assignRole('noaccess'))->save();
        $items = collect([
            EntryFactory::collection('test')->slug('alfa')->create(),
            EntryFactory::collection('test')->slug('bravo')->create(),
        ]);

        $this->assertTrue((new DuplicateEntry)->authorize($userWithPermission, $items->first()));
        $this->assertTrue((new DuplicateEntry)->authorizeBulk($userWithPermission, $items));
        $this->assertFalse((new DuplicateEntry)->authorize($userWithoutPermission, $items->first()));
        $this->assertFalse((new DuplicateEntry)->authorizeBulk($userWithoutPermission, $items));
    }

    /** @test */
    public function it_respects_the_collection_not_requiring_slugs()
    {
        Collection::make('test')->requiresSlugs(false)->save();
        EntryFactory::id('alfa-id')->collection('test')->data(['title' => 'Alfa'])->create();
        EntryFactory::id('bravo-id')->collection('test')->data(['title' => 'Bravo'])->create();
        EntryFactory::id('charlie-id')->collection('test')->data(['title' => 'Charlie'])->create();

        $this->assertEquals([
            ['slug' => null, 'published' => true, 'data' => ['title' => 'Alfa']],
            ['slug' => null, 'published' => true, 'data' => ['title' => 'Bravo']],
            ['slug' => null, 'published' => true, 'data' => ['title' => 'Charlie']],
        ], $this->entryData());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
            Entry::find('charlie-id'),
        ]), collect());

        $alfaDuplicate = Entry::query()->where('duplicated_from', 'alfa-id')->first();

        $this->assertNull($alfaDuplicate->slug());
        $this->assertNotEquals('alfa-id', $id = $alfaDuplicate->id());
        $this->assertEquals($id.'.md', basename($alfaDuplicate->path()));

        $charlieDuplicate = Entry::query()->where('duplicated_from', 'charlie-id')->first();

        $this->assertNull($charlieDuplicate->slug());
        $this->assertNotEquals('charlie-id', $id = $charlieDuplicate->id());
        $this->assertEquals($id.'.md', basename($charlieDuplicate->path()));
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
