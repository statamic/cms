<?php

namespace Tests\Actions;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
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

    #[Test]
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

    #[Test]
    #[DataProvider('authorizationProvider')]
    public function it_authorizes(
        bool $isMultisite,
        array $permissions,
        bool $expectedToBeAuthorized
    ) {
        $this->setTestRoles(['test' => $permissions]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = Collection::make('test');

        if ($isMultisite) {
            $this->setSites([
                'en' => ['url' => '/', 'locale' => 'en'],
                'fr' => ['url' => '/fr/', 'locale' => 'fr'],
            ]);

            $collection->sites(['en', 'fr']);
        }

        $collection->save();

        $item = EntryFactory::collection('test')->slug('alfa')->locale('en')->create();

        $this->assertEquals($expectedToBeAuthorized, (new DuplicateEntry)->authorize($user, $item));
    }

    public static function authorizationProvider()
    {
        return [
            'no permission' => [
                $multisite = false,
                $permissions = [],
                $expectedToBeAuthorized = false,
            ],
            'permission to create, access to no sites, but not using multisite' => [
                $multisite = false,
                $permissions = ['create test entries'],
                $expectedToBeAuthorized = true,
            ],
            'permission to create, access to site' => [
                $multisite = true,
                $permissions = ['create test entries', 'access en site'],
                $expectedToBeAuthorized = true,
            ],
            'permission to create, access to no sites' => [
                $multisite = true,
                $permissions = ['create test entries'],
                $expectedToBeAuthorized = false,
            ],
            'permission to create, access to a different site' => [
                $multisite = true,
                $permissions = ['create test entries', 'access fr site'],
                $expectedToBeAuthorized = false,
            ],
        ];
    }

    #[Test]
    #[DataProvider('bulkAuthorizationProvider')]
    public function it_authorizes_in_bulk(
        bool $isMultisite,
        array $permissions,
        bool $expectedToBeAuthorized
    ) {
        $this->setTestRoles(['test' => $permissions]);
        $user = tap(User::make()->assignRole('test'))->save();

        $collection = Collection::make('test');

        if ($isMultisite) {
            $this->setSites([
                'en' => ['url' => '/', 'locale' => 'en'],
                'fr' => ['url' => '/fr/', 'locale' => 'fr'],
                'de' => ['url' => '/de/', 'locale' => 'de'],
            ]);

            $collection->sites(['en', 'fr', 'de']);
        }

        $collection->save();

        $items = collect([
            EntryFactory::collection('test')->slug('alfa')->locale('en')->create(),
            EntryFactory::collection('test')->slug('bravo')->locale($isMultisite ? 'fr' : 'en')->create(),
        ]);

        $this->assertEquals($expectedToBeAuthorized, (new DuplicateEntry)->authorizeBulk($user, $items));
    }

    public static function bulkAuthorizationProvider()
    {
        return [
            'no permission' => [
                $multisite = false,
                $permissions = [],
                $expectedToBeAuthorized = false,
            ],
            'permission to create, access to no sites, but not using multisite' => [
                $multisite = false,
                $permissions = ['create test entries'],
                $expectedToBeAuthorized = true,
            ],
            'permission to create, access to all sites' => [
                $multisite = true,
                $permissions = ['create test entries', 'access en site', 'access fr site'],
                $expectedToBeAuthorized = true,
            ],
            'permission to create, access to no sites' => [
                $multisite = true,
                $permissions = ['create test entries'],
                $expectedToBeAuthorized = false,
            ],
            'permission to create, access to a site that the entries arent in' => [
                $multisite = true,
                $permissions = ['create test entries', 'access de site'],
                $expectedToBeAuthorized = false,
            ],
        ];
    }

    #[Test]
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

    #[Test]
    public function it_duplicates_an_entry_with_localizations()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
            'de' => ['url' => 'http://domain.com/de/', 'locale' => 'de'], // Add additional site that the entry doesn't exist in, to ensure it doesn't get duplicated into it.
            'es' => ['url' => 'http://domain.com/es/', 'locale' => 'es'],
        ]);

        Collection::make('test')->sites(['en', 'fr', 'de', 'es'])->save();

        $entry = EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        $entry->makeLocalization('fr')->id('alfa-id-fr')->data(['title' => 'Alfa (French)'])->save();
        $entry->makeLocalization('es')->id('alfa-id-es')->data(['title' => 'Alfa (Spanish)'])->save();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => ''],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (Spanish)'], 'locale' => 'es', 'origin' => 'en.alfa'],
        ], $this->entryData());

        // Make super user since this test isn't concerned with permissions.
        $this->actingAs(tap(User::make()->makeSuper())->save());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
        ]), []);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (Spanish)'], 'locale' => 'es', 'origin' => 'en.alfa'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (French) (Duplicated)', 'duplicated_from' => 'alfa-id-fr'], 'locale' => 'fr', 'origin' => 'en.alfa-1'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Spanish) (Duplicated)', 'duplicated_from' => 'alfa-id-es'], 'locale' => 'es', 'origin' => 'en.alfa-1'],
        ], $this->entryData());
    }

    #[Test]
    public function it_duplicates_an_entry_with_nested_localizations()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
            'fr_ca' => ['url' => 'http://domain.com/fr-ca/', 'locale' => 'fr_CA'],
            'es' => ['url' => 'http://domain.com/es/', 'locale' => 'es'],
        ]);

        Collection::make('test')->sites(['en', 'fr', 'fr_ca', 'es'])->save();

        $entry = EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        $entry->makeLocalization('es')->id('alfa-id-es')->data(['title' => 'Alfa (Spanish)'])->save();
        $french = tap($entry->makeLocalization('fr')->id('alfa-id-fr')->data(['title' => 'Alfa (French)']))->save();
        $french->makeLocalization('fr_ca')->id('alfa-id-fr-ca')->data(['title' => 'Alfa (French Canadian)'])->save();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => ''],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (Spanish)'], 'locale' => 'es', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French Canadian)'], 'locale' => 'fr_ca', 'origin' => 'fr.alfa'],
        ], $this->entryData());

        $this->setTestRoles(['test' => [
            'create test entries',
            'access en site',
            'access es site',
            'access fr site',
            'access fr_ca site',
        ]]);

        $this->actingAs(tap(User::make()->assignRole('test'))->save());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
        ]), []);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (Spanish)'], 'locale' => 'es', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French Canadian)'], 'locale' => 'fr_ca', 'origin' => 'fr.alfa'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Spanish) (Duplicated)', 'duplicated_from' => 'alfa-id-es'], 'locale' => 'es', 'origin' => 'en.alfa-1'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (French) (Duplicated)', 'duplicated_from' => 'alfa-id-fr'], 'locale' => 'fr', 'origin' => 'en.alfa-1'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (French Canadian) (Duplicated)', 'duplicated_from' => 'alfa-id-fr-ca'], 'locale' => 'fr_ca', 'origin' => 'fr.alfa-1'],
        ], $this->entryData());
    }

    #[Test]
    public function it_only_duplicates_authorized_localizations()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
            'es' => ['url' => 'http://domain.com/es/', 'locale' => 'es'],
        ]);

        Collection::make('test')->sites(['en', 'fr', 'es'])->save();

        $entry = EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        $entry->makeLocalization('fr')->id('alfa-id-fr')->data(['title' => 'Alfa (French)'])->save();
        $entry->makeLocalization('es')->id('alfa-id-es')->data(['title' => 'Alfa (Spanish)'])->save();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => ''],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (Spanish)'], 'locale' => 'es', 'origin' => 'en.alfa'],
        ], $this->entryData());

        $this->setTestRoles(['test' => [
            'create test entries',
            'access en site',
            'access es site',
        ]]);

        $this->actingAs(tap(User::make()->assignRole('test'))->save());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
        ]), []);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (Spanish)'], 'locale' => 'es', 'origin' => 'en.alfa'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Spanish) (Duplicated)', 'duplicated_from' => 'alfa-id-es'], 'locale' => 'es', 'origin' => 'en.alfa-1'],
        ], $this->entryData());
    }

    #[Test]
    public function it_doesnt_duplicate_authorized_descendants_of_unauthorized_localizations()
    {
        // 🤯

        $this->setSites([
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
            'fr_ca' => ['url' => 'http://domain.com/fr-ca/', 'locale' => 'fr_CA'],
            'es' => ['url' => 'http://domain.com/es/', 'locale' => 'es'],
        ]);

        Collection::make('test')->sites(['en', 'fr', 'fr_ca', 'es'])->save();

        $entry = EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        $entry->makeLocalization('es')->id('alfa-id-es')->data(['title' => 'Alfa (Spanish)'])->save();
        $french = tap($entry->makeLocalization('fr')->id('alfa-id-fr')->data(['title' => 'Alfa (French)']))->save();
        $french->makeLocalization('fr_ca')->id('alfa-id-fr-ca')->data(['title' => 'Alfa (French Canadian)'])->save();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => ''],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (Spanish)'], 'locale' => 'es', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French Canadian)'], 'locale' => 'fr_ca', 'origin' => 'fr.alfa'],
        ], $this->entryData());

        $this->setTestRoles(['test' => [
            'create test entries',
            'access en site',
            'access es site',
            // 'access fr site', // no permission for fr
            'access fr_ca site',
        ]]);

        $this->actingAs(tap(User::make()->assignRole('test'))->save());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id'),
        ]), []);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (Spanish)'], 'locale' => 'es', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa (French Canadian)'], 'locale' => 'fr_ca', 'origin' => 'fr.alfa'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Spanish) (Duplicated)', 'duplicated_from' => 'alfa-id-es'], 'locale' => 'es', 'origin' => 'en.alfa-1'],
            // No french *or* french canadian, even though french canadian is authorized.
        ], $this->entryData());
    }

    #[Test]
    public function it_duplicates_an_entry_from_a_non_default_site()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
        ]);

        Collection::make('test')->sites(['en', 'fr'])->save();

        EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        EntryFactory::id('bravo-id')->locale('fr')->collection('test')->slug('bravo')->data(['title' => 'Bravo'])->create();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'bravo', 'published' => true, 'data' => ['title' => 'Bravo'], 'locale' => 'fr', 'origin' => null],
        ], $this->entryData());

        // Make super user since this test isn't concerned with permissions.
        $this->actingAs(tap(User::make()->makeSuper())->save());

        (new DuplicateEntry)->run(collect([
            Entry::find('bravo-id'),
        ]), []);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'bravo', 'published' => true, 'data' => ['title' => 'Bravo'], 'locale' => 'fr', 'origin' => null],
            ['slug' => 'bravo-1', 'published' => false, 'data' => ['title' => 'Bravo (Duplicated)', 'duplicated_from' => 'bravo-id'], 'locale' => 'fr', 'origin' => null],
        ], $this->entryData());
    }

    #[Test]
    public function if_an_entry_has_an_origin_it_duplicates_the_root_origin()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
        ]);

        Collection::make('test')->sites(['en', 'fr'])->save();

        $entry = EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        $entry->makeLocalization('fr')->id('alfa-id-fr')->slug('alfa-fr')->data(['title' => 'Alfa (French)'])->save();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-fr', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
        ], $this->entryData());

        // Make super user since this test isn't concerned with permissions.
        $this->actingAs(tap(User::make()->makeSuper())->save());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id-fr'),
        ]), []);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-fr', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-fr-1', 'published' => false, 'data' => ['title' => 'Alfa (French) (Duplicated)', 'duplicated_from' => 'alfa-id-fr'], 'locale' => 'fr', 'origin' => 'en.alfa-1'],
        ], $this->entryData());
    }

    #[Test]
    public function if_an_entry_has_an_origin_and_the_root_origin_is_also_selected_it_only_duplicates_the_root_origin()
    {
        $this->setSites([
            'en' => ['url' => 'http://domain.com/', 'locale' => 'en'],
            'fr' => ['url' => 'http://domain.com/fr/', 'locale' => 'fr'],
        ]);

        Collection::make('test')->sites(['en', 'fr'])->save();

        $entry = EntryFactory::id('alfa-id')->locale('en')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
        $entry->makeLocalization('fr')->id('alfa-id-fr')->slug('alfa-fr')->data(['title' => 'Alfa (French)'])->save();

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-fr', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
        ], $this->entryData());

        // Make super user since this test isn't concerned with permissions.
        $this->actingAs(tap(User::make()->makeSuper())->save());

        (new DuplicateEntry)->run(collect([
            Entry::find('alfa-id-fr'),
            Entry::find('alfa-id'),
        ]), []);

        $this->assertEquals([
            ['slug' => 'alfa', 'published' => true, 'data' => ['title' => 'Alfa'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-fr', 'published' => true, 'data' => ['title' => 'Alfa (French)'], 'locale' => 'fr', 'origin' => 'en.alfa'],
            ['slug' => 'alfa-1', 'published' => false, 'data' => ['title' => 'Alfa (Duplicated)', 'duplicated_from' => 'alfa-id'], 'locale' => 'en', 'origin' => null],
            ['slug' => 'alfa-fr-1', 'published' => false, 'data' => ['title' => 'Alfa (French) (Duplicated)', 'duplicated_from' => 'alfa-id-fr'], 'locale' => 'fr', 'origin' => 'en.alfa-1'],
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
