<?php

namespace Tests\Stache;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Stache\Stores\CollectionsStore;
use Statamic\Stache\Stores\EntriesStore;

class StacheTest extends TestCase
{
    protected $stache;

    public function setUp(): void
    {
        $this->stache = new Stache;
    }

    /** @test */
    public function sites_can_be_defined_and_retrieved()
    {
        $this->assertNull($this->stache->sites());

        $return = $this->stache->sites(['one', 'two']);

        $this->assertEquals($this->stache, $return);
        $this->assertInstanceOf(Collection::class, $this->stache->sites());
        $this->assertEquals(['one', 'two'], $this->stache->sites()->all());
    }

    /** @test */
    public function default_site_can_be_retrieved()
    {
        $this->stache->sites(['foo', 'bar']);

        $this->assertEquals('foo', $this->stache->defaultSite());
    }

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function it_generates_an_id()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_clears_its_cache()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_refreshes_itself()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_gets_its_cache_file_size()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_record_its_build_time()
    {
        $this->markTestIncomplete();
    }
}
