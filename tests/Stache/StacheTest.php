<?php

namespace Tests\Stache;

use Facades\Tests\Factories\EntryFactory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blink;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\EntriesStore;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class StacheTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    protected $stache;

    public function setUp(): void
    {
        parent::setUp();
        $this->stache = new Stache;
    }

    #[Test]
    public function sites_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->sites());

        $return = $this->stache->sites(['one', 'two']);

        $this->assertEquals($this->stache, $return);
        $this->assertInstanceOf(Collection::class, $this->stache->sites());
        $this->assertEquals(['one', 'two'], $this->stache->sites()->all());
    }

    #[Test]
    public function default_site_can_be_retrieved()
    {
        $this->stache->sites(['foo', 'bar']);

        $this->assertEquals('foo', $this->stache->defaultSite());
    }

    #[Test]
    public function stores_can_be_registered()
    {
        $this->stache->sites(['en']); // store expects the stache to have site(s)
        $this->assertTrue($this->stache->stores()->isEmpty());

        $return = $this->stache->registerStore(
            new CollectionsStore($this->stache, \Mockery::mock(Filesystem::class))
        );

        $this->assertEquals($this->stache, $return);
        tap($this->stache->stores(), function ($stores) {
            $this->assertEquals(1, $stores->count());
            $this->assertEquals('collections', $stores->keys()->first());
            $this->assertInstanceOf(CollectionsStore::class, $stores->first());
            $this->assertInstanceOf(CollectionsStore::class, $this->stache->store('collections'));
        });
    }

    #[Test]
    public function multiple_stores_can_be_registered_at_once()
    {
        $this->stache->sites(['en']); // store expects the stache to have site(s)
        $this->assertTrue($this->stache->stores()->isEmpty());

        $return = $this->stache->registerStores([
            new CollectionsStore($this->stache, \Mockery::mock(Filesystem::class)),
            new EntriesStore($this->stache, \Mockery::mock(Filesystem::class)),
        ]);

        $this->assertEquals($this->stache, $return);
        tap($this->stache->stores(), function ($stores) {
            $this->assertEquals(2, $stores->count());
            $this->assertEquals(['collections', 'entries'], $stores->keys()->all());
            $this->assertInstanceOf(CollectionsStore::class, $stores['collections']);
            $this->assertInstanceOf(EntriesStore::class, $stores['entries']);
            $this->assertInstanceOf(CollectionsStore::class, $this->stache->store('collections'));
            $this->assertInstanceOf(EntriesStore::class, $this->stache->store('entries'));
        });
    }

    #[Test]
    public function an_aggregate_stores_child_store_can_be_retrieved_directly()
    {
        $this->stache->sites(['en']); // stores expect the stache to have site(s)
        $store = (new EntriesStore($this->stache, \Mockery::mock(Filesystem::class)))->setChildStoreCreator(function () {
            return new ChildStore($this->stache, \Mockery::mock(Filesystem::class));
        });
        $one = $store->store('one');
        $two = $store->store('two');
        $this->stache->registerStore($store);

        $this->assertEquals($one, $this->stache->store('entries::one'));
        $this->assertEquals($two, $this->stache->store('entries::two'));
    }

    #[Test]
    public function it_generates_an_id()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_clears_its_cache()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_refreshes_itself()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_gets_its_cache_file_size()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function it_can_record_its_build_time()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    #[DataProvider('watcherProvider')]
    public function it_can_determine_if_watcher_is_enabled($environment, $config, $expected)
    {
        app()['env'] = $environment;

        config(['statamic.stache.watcher' => $config]);

        $this->assertEquals($expected, $this->stache->isWatcherEnabled());
    }

    public static function watcherProvider()
    {
        return [
            ['local', 'config' => true, 'expected' => true],
            ['production', 'config' => true, 'expected' => true],
            ['local', 'config' => false, 'expected' => false],
            ['production', 'config' => false, 'expected' => false],
            ['local', 'config' => 'auto', 'expected' => true],
            ['production', 'config' => 'auto', 'expected' => false],
            ['other', 'config' => 'auto', 'expected' => false],
            ['local', 'config' => null, 'expected' => false],
            ['production', 'config' => null, 'expected' => false],
        ];
    }

    // #[Test]
    // public function stache_maintains_sync_with_cli_commands()
    // {
    //     // Using the filesystem cache may make it easier for us to simulate this issue.
    //     config(['cache.default' => 'file']);
    //     Cache::clear();

    //     // Register an artisan command to simulate stache being out of sync with CLI commands.
    //     Artisan::command('testing:stache-sync', function () {
    //         // Serialise the entry query builder so that it can be replaced with what
    //         // _would_ be in memory for a separate process/thread.
    //         $serialisedQueryBuilder = serialize(app(\Statamic\Stache\Query\EntryQueryBuilder::class));

    //         // Delete a couple of entries.
    //         CollectionFacade::find('test')->queryEntries()->whereIn('slug', [
    //             'donkus',
    //             'eggbert',
    //         ])->get()->each->delete();

    //         // Swap in the original `EntryQueryBuilder`.
    //         app()->bind(\Statamic\Stache\Query\EntryQueryBuilder::class, function () use ($serialisedQueryBuilder) {
    //             return unserialize($serialisedQueryBuilder);
    //         });
    //     });

    //     CollectionFacade::make('test')->save();
    //     $collection = CollectionFacade::find('test');

    //     // Create some entries.
    //     EntryFactory::id('alfa-id')->collection('test')->slug('alfa')->data(['title' => 'Alfa'])->create();
    //     EntryFactory::id('bravo-id')->collection('test')->slug('bravo')->data(['title' => 'Bravo'])->create();
    //     EntryFactory::id('charlie-id')->collection('test')->slug('charlie')->data(['title' => 'Charlie'])->create();
    //     EntryFactory::id('donkus-id')->collection('test')->slug('donkus')->data(['title' => 'Donkus'])->create();
    //     EntryFactory::id('eggbert-id')->collection('test')->slug('eggbert')->data(['title' => 'Eggbert'])->create();

    //     // Fetch entries and make sure that they all have titles.
    //     $entriesBefore = $collection->queryEntries()->get();
    //     $this->assertEquals(5, $entriesBefore->count());
    //     $this->assertEmpty($entriesBefore->pluck('title')->reject(fn ($title) => (bool) $title));

    //     // $serialisedQueryBuilder = serialize(app(\Statamic\Stache\Query\EntryQueryBuilder::class));

    //     $this->artisan('testing:stache-sync');

    //     // $this->app->bind(\Statamic\Stache\Query\EntryQueryBuilder::class, function () use ($serialisedQueryBuilder) {
    //     //     return unserialize($serialisedQueryBuilder);
    //     // });

    //     // \Illuminate\Support\Facades\Request::swap(new \Tests\Fakes\FakeArtisanRequest('queue:work'));

    //     $entriesAfter = $collection->queryEntries()->get();
    //     $this->assertEquals(3, $entriesAfter->count());
    //     $this->assertEmpty($entriesAfter->pluck('title')->reject(fn ($title) => (bool) $title));
    // }
}
