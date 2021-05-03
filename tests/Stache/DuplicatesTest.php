<?php

namespace Tests\Stache;

use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Entries\Entry;
use Statamic\Stache\Duplicates;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Stache\Stores\Store;
use Tests\TestCase;

class DuplicatesTest extends TestCase
{
    /** @test */
    public function it_tracks_duplicates()
    {
        $original = $this->mock(Entry::class);
        $original->shouldReceive('path')->andReturn('path/to/original.md');

        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');
        $store->shouldReceive('getItem')->with('123')->andReturn($original);

        $stache = $this->mock(Stache::class);
        $stache->shouldReceive('store')->with('test-store')->andReturn($store);

        $duplicates = new Duplicates($stache);

        $this->assertEquals([], $duplicates->all()->all());

        $duplicates->track($store, '123', 'path/to/duplicate.md');

        // Do it twice to make sure it's only tracked once.
        $duplicates->track($store, '123', 'path/to/duplicate.md');

        $this->assertEquals([
            'test-store' => [
                '123' => [
                    'path/to/original.md',
                    'path/to/duplicate.md',
                ],
            ],
        ], $duplicates->all()->all());

        $duplicates->track($store, '123', 'path/to/triplicate.md');

        $this->assertEquals([
            'test-store' => [
                '123' => [
                    'path/to/original.md',
                    'path/to/duplicate.md',
                    'path/to/triplicate.md',
                ],
            ],
        ], $duplicates->all()->all());
    }

    /** @test */
    public function it_saves_to_the_cache()
    {
        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $stache = $this->mock(Stache::class);
        $stache->shouldReceive('store')->with('test-store')->andReturn($store);

        $duplicates = new Duplicates($stache);

        $duplicates->track($store, '123', 'path/to/duplicate.md');

        $this->assertNull(Cache::get('stache::duplicates'));

        $duplicates->cache();

        $this->assertEquals([
            'test-store' => [
                '123' => [
                    'path/to/duplicate.md',
                ],
            ],
        ], Cache::get('stache::duplicates'));
    }

    /** @test */
    public function it_doesnt_save_if_there_are_no_changes()
    {
        $duplicates = new Duplicates($this->mock(Stache::class));

        $this->assertNull(Cache::get('stache::duplicates'));

        $duplicates->cache();

        $this->assertNull(Cache::get('stache::duplicates'));
    }

    /** @test */
    public function it_loads_from_cache()
    {
        Cache::forever('stache::duplicates', ['foo' => 'bar']);

        $duplicates = new Duplicates($this->mock(Stache::class));

        $return = $duplicates->load();

        $this->assertEquals($duplicates, $return);
        $this->assertEquals(['foo' => 'bar'], $duplicates->getItems());
    }

    /** @test */
    public function it_clears_the_cache()
    {
        Cache::forever('stache::duplicates', ['foo' => 'bar']);

        $duplicates = new Duplicates($this->mock(Stache::class));

        $this->assertNotNull(Cache::get('stache::duplicates'));

        $duplicates->clear();

        $this->assertNull(Cache::get('stache::duplicates'));
    }

    /** @test */
    public function it_finds_all_the_duplicates()
    {
        $store1 = $this->mock(Store::class);
        $store1->shouldReceive('paths')->once();
        $store1->shouldReceive('clearCachedPaths')->once();

        $store2 = $this->mock(Store::class);
        $store2->shouldReceive('paths')->once();
        $store2->shouldReceive('clearCachedPaths')->once();

        $childStore = $this->mock(ChildStore::class);
        $childStore->shouldReceive('paths')->once();
        $childStore->shouldReceive('clearCachedPaths')->once();

        $store3 = $this->mock(AggregateStore::class);
        $store3->shouldReceive('discoverStores')->once()->andReturn([$childStore]);

        $stache = $this->mock(Stache::class);
        $stache->shouldReceive('stores')->andReturn(collect([$store1, $store2, $store3]));

        $duplicates = (new Duplicates($stache));

        $return = $duplicates->find();

        $this->assertEquals($duplicates, $return);
    }

    /** @test */
    public function it_checks_if_a_duplicate_exists()
    {
        $stache = $this->mock(Stache::class);

        $duplicates = (new Duplicates($stache))->setItems([
            'test-store' => [
                '123' => [
                    'path/to/duplicate.md',
                ],
            ],
        ]);

        $this->assertTrue($duplicates->has('path/to/duplicate.md'));
        $this->assertFalse($duplicates->has('path/to/another.md'));
    }
}
