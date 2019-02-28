<?php

namespace Tests\Stache;

use Statamic\Stache\Stache;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Stache\Stores\EntriesStore;
use Statamic\Stache\Stores\CollectionsStore;

class StacheTest extends TestCase
{
    public function setUp()
    {
        $this->stache = new Stache;
        $this->stache->setBootstrapper($this->bootstrapper = new CountableFakeBootstrapper);
    }

    /** @test */
    function it_can_be_heated_and_cooled()
    {
        $this->assertTrue($this->stache->isCold());
        $this->assertFalse($this->stache->isWarm());

        $this->stache->heat();

        $this->assertTrue($this->stache->isWarm());
        $this->assertFalse($this->stache->isCold());

        $this->stache->cool();

        $this->assertFalse($this->stache->isWarm());
        $this->assertTrue($this->stache->isCold());
    }

    /** @test */
    function sites_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->sites());

        $return = $this->stache->sites(['one', 'two']);

        $this->assertEquals($this->stache, $return);
        $this->assertInstanceOf(Collection::class, $this->stache->sites());
        $this->assertEquals(['one', 'two'], $this->stache->sites()->all());
    }

    /** @test */
    function default_site_can_be_retrieved()
    {
        $this->stache->sites(['foo', 'bar']);

        $this->assertEquals('foo', $this->stache->defaultSite());
    }

    /** @test */
    function cached_keys_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->keys());

        $return = $this->stache->keys(['foo', 'bar']);

        $this->assertEquals($this->stache, $return);
        $this->assertInstanceOf(Collection::class, $this->stache->keys());
        $this->assertEquals(['foo', 'bar'], $this->stache->keys()->all());
    }

    /** @test */
    function config_data_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->config());

        $return = $this->stache->config(['foo', 'bar']);

        $this->assertEquals($this->stache, $return);
        $this->assertEquals(['foo', 'bar'], $this->stache->config());
    }

    /** @test */
    function stores_can_be_registered()
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

    /** @test */
    function multiple_stores_can_be_registered_at_once()
    {
        $this->stache->sites(['en']); // store expects the stache to have site(s)
        $this->assertTrue($this->stache->stores()->isEmpty());

        $return = $this->stache->registerStores([
            new CollectionsStore($this->stache, \Mockery::mock(Filesystem::class)),
            new EntriesStore($this->stache, \Mockery::mock(Filesystem::class))
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

    /** @test */
    function an_aggregate_stores_child_store_can_be_retrieved_directly()
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

    /** @test */
    function it_does_not_boot_more_than_once()
    {
        $this->assertFalse($this->stache->hasBooted());

        $return = $this->stache->boot();

        $this->assertTrue($this->stache->hasBooted());
        $this->assertEquals($this->stache, $return);

        $return = $this->stache->boot();

        $this->assertEquals(1, $this->bootstrapper->boots);
        $this->assertEquals($this->stache, $return);
    }

    /** @test */
    function stache_is_booted_on_demand_when_attempting_to_access_stores()
    {
        $this->assertFalse($this->stache->hasBooted());

        $this->stache->stores();

        $this->assertTrue($this->stache->hasBooted());
    }

    /** @test */
    function stache_is_booted_on_demand_when_attempting_to_access_a_single_store()
    {
        $this->assertFalse($this->stache->hasBooted());

        $this->stache->store('one');

        $this->assertTrue($this->stache->hasBooted());
    }

    /** @test */
    function stache_is_booted_on_demand_when_attempting_to_access_an_aggregate_stores_child_store()
    {
        $this->stache->sites(['en']); // stores expect the stache to have site(s)
        $store = (new EntriesStore($this->stache, \Mockery::mock(Filesystem::class)))->setChildStoreCreator(function () {
            return new ChildStore($this->stache, \Mockery::mock(Filesystem::class));
        });
        $one = $store->store('one');
        $this->stache->registerStore($store);
        $this->assertFalse($this->stache->hasBooted());

        $this->stache->store('entries::one');

        $this->assertTrue($this->stache->hasBooted());
    }

    /** @test */
    function booting_can_be_disabled_and_reenabled()
    {
        $this->assertFalse($this->stache->hasBooted());

        $return = $this->stache->disableBooting();
        $this->stache->boot();
        $this->assertFalse($this->stache->hasBooted());
        $this->assertEquals(0, $this->bootstrapper->boots);
        $this->assertEquals($this->stache, $return);

        $return = $this->stache->enableBooting();
        $this->stache->boot();
        $this->assertTrue($this->stache->hasBooted());
        $this->assertEquals(1, $this->bootstrapper->boots);
        $this->assertEquals($this->stache, $return);
    }

    /** @test */
    function callback_can_be_run_without_booting_the_stache()
    {
        $this->assertFalse($this->stache->hasBooted());

        $callbackRan = false;
        $return = $this->stache->withoutBooting(function ($stache) use (&$callbackRan) {
            $this->assertEquals($this->stache, $stache);
            $this->stache->boot();
            $callbackRan = true;
        });

        $this->assertTrue($callbackRan);
        $this->assertFalse($this->stache->hasBooted());
        $this->assertEquals(0, $this->bootstrapper->boots);
        $this->assertEquals($this->stache, $return);
    }

    /** @test */
    function it_gets_a_map_of_ids_to_the_stores()
    {
        $this->stache->sites(['en']); // stores expect the stache to have site(s)
        $this->stache->registerStore(new class($this->stache, \Mockery::mock(Filesystem::class)) extends CollectionsStore {
            public function key() { return 'one'; }
            public function getIdMap() { return collect(['id-1' => 'one', 'id-2' => 'one']); }
        });
        $this->stache->registerStore(new class($this->stache, \Mockery::mock(Filesystem::class)) extends CollectionsStore {
            public function key() { return 'two'; }
            public function getIdMap() { return collect(['id-3' => 'two', 'id-4' => 'two']); }
        });
        $this->stache->registerStore(new class($this->stache, \Mockery::mock(Filesystem::class)) extends CollectionsStore {
            public function key() { return 'three'; }
            public function getIdMap() { return collect(['id-5' => 'three::one', 'id-6' => 'three::two']); }
        });

        $this->assertEquals([
            'id-1' => 'one',
            'id-2' => 'one',
            'id-3' => 'two',
            'id-4' => 'two',
            'id-5' => 'three::one',
            'id-6' => 'three::two',
        ], $this->stache->idMap()->all());
    }

    /** @test */
    function it_gets_a_store_by_id()
    {
        $this->stache->sites(['en']); // stores expect the stache to have site(s)
        $entriesStore = (new EntriesStore($this->stache, \Mockery::mock(Filesystem::class)))->setChildStoreCreator(function () {
            return new ChildStore($this->stache, \Mockery::mock(Filesystem::class));
        });
        $entriesOne = $entriesStore->store('one');
        $entriesTwo = $entriesStore->store('two');
        $this->stache->registerStores([
            $collectionsStore = new CollectionsStore($this->stache, \Mockery::mock(Filesystem::class)),
            $entriesStore
        ]);
        $entriesOne->setSitePath('en', '123', '/onetwothree.md');
        $entriesTwo->setSitePath('en', '456', '/fourfivesix.md');
        $collectionsStore->setSitePath('en', '789', '/seveneightnine.md');

        $this->assertEquals($entriesOne, $this->stache->getStoreById('123'));
        $this->assertEquals($entriesTwo, $this->stache->getStoreById('456'));
        $this->assertEquals($collectionsStore, $this->stache->getStoreById('789'));
    }

    /** @test */
    function it_queues_timestamp_cache_payloads_for_persistence()
    {
        $this->assertEquals([], $this->stache->queuedTimestampCaches()->all());

        $this->stache->queueTimestampCache('one', ['foo' => 'bar']);
        $this->stache->queueTimestampCache('two', ['baz' => 'qux']);

        $this->assertEquals([
            'one' => ['foo' => 'bar'],
            'two' => ['baz' => 'qux'],
        ], $this->stache->queuedTimestampCaches()->all());
    }
}

class CountableFakeBootstrapper
{
    public $boots = 0;
    public function boot()
    {
        $this->boots++;
    }
}
