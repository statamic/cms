<?php

namespace Tests\Actions;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Actions\DuplicateEntry;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Tests\FakesRoles;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class DuplicateEntryTest extends TestCase
{
    use FakesRoles;
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
        ]), []);

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
        ]), []);

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
        ]), []);

        $alfaDuplicate = Entry::query()->where('duplicated_from', 'alfa-id')->first();

        $this->assertNull($alfaDuplicate->slug());
        $this->assertNotEquals('alfa-id', $id = $alfaDuplicate->id());
        $this->assertEquals($id.'.md', basename($alfaDuplicate->path()));

        $charlieDuplicate = Entry::query()->where('duplicated_from', 'charlie-id')->first();

        $this->assertNull($charlieDuplicate->slug());
        $this->assertNotEquals('charlie-id', $id = $charlieDuplicate->id());
        $this->assertEquals($id.'.md', basename($charlieDuplicate->path()));
    }

    /** @test */
    public function it_duplicates_an_entry_without_localizations()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
        ]]);

        Collection::make('test')->sites(['en', 'fr'])->save();

        $entry = EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        $entry->makeLocalization('fr')->id('alfa-id-fr')->data(['title' => 'Alfa (French)'])->save();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
        ], $this->entryData());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
        ]), [
            'mode' => 'current',
        ]);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id'], 'locale' => 'en', 'origin' => null],
        ], $this->entryData());
    }

    /** @test */
    public function it_duplicates_an_entry_with_all_its_localizations()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
        ]]);

        Collection::make('test')->sites(['en', 'fr'])->save();

        $entry = EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        $entry->makeLocalization('fr')->id('alfa-id-fr')->data(['title' => 'Alfa (French)'])->save();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => ''],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
        ], $this->entryData());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
        ]), [
            'mode' => 'all',
        ]);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (French) (Duplicated)', 'duplicated_from' => 'alfa-id-fr'], 'locale' => 'fr', 'origin' => 'en.alfa-1'],
        ], $this->entryData());
    }

    /** @test */
    public function it_duplicates_an_entry_from_a_non_default_site()
    {
        Site::setConfig(['sites' => [
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
        ]]);

        Collection::make('test')->sites(['en', 'fr'])->save();

        EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        EntryFactory::id('bravo-id')->locale('fr')->collection('test')->slug('bravo')->data(['title' => 'Bravo'])->create();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'bravo', 'published' => true, 'data' => ['title' => 'Bravo'], 'locale' => 'fr', 'origin' => null],
        ], $this->entryData());

        (new DuplicateEntry)->run(collect([
            Entry::find('bravo-id'),
        ]), [
            'mode' => 'current',
        ]);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'bravo', 'published' => true, 'data' => ['title' => 'Bravo'], 'locale' => 'fr', 'origin' => null],
            ['slug' => 'bravo-1', 'published' => false, 'data' => ['title' => 'Bravo (Duplicated)', 'duplicated_from' => 'bravo-id'], 'locale' => 'fr', 'origin' => null],
        ], $this->entryData());
    }

    private function entryData()
    {
        return Entry::all()->map(function ($entry) {
            $arr = [
                'slug' => $entry->slug(),
                'published' => $entry->published(),
                'data' => $entry->data()->all(),
            ];

            if (Site::hasMultiple()) {
                $arr['locale'] = $entry->locale();
                // Use locale.slug string instead of id since we won't always know it in advance
                $arr['origin'] = $entry->hasOrigin() ? $entry->origin()->locale().'.'.$entry->origin()->slug() : null;
            }

            return $arr;
        })->all();
    }
}
