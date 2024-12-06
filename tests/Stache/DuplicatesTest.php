<?php

namespace Tests\Stache;

use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Contracts\Entries\Entry;
use Statamic\Stache\Duplicates;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\AggregateStore;
use Statamic\Stache\Stores\ChildStore;
use Statamic\Stache\Stores\Store;
use Tests\TestCase;

class DuplicatesTest extends TestCase
{
    #[Test]
    public function it_tracks_duplicates()
    {
        $original = $this->mock(Entry::class);
        $original->shouldReceive('path')->andReturn('path/to/original.md');

        $anotherOriginal = $this->mock(Entry::class);
        $anotherOriginal->shouldReceive('path')->andReturn('path/to/another-original.md');

        $thirdOriginal = $this->mock(Entry::class);
        $thirdOriginal->shouldReceive('path')->andReturn('path/to/yet/another-original.md');

        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');
        $store->shouldReceive('getItem')->with('123')->andReturn($original);
        $store->shouldReceive('getItem')->with('456')->andReturn($anotherOriginal);

        $anotherStore = $this->mock(Store::class);
        $anotherStore->shouldReceive('key')->andReturn('another-test-store');
        $anotherStore->shouldReceive('getItem')->with('789')->andReturn($thirdOriginal);

        $stache = $this->mock(Stache::class);
        $stache->shouldReceive('store')->with('test-store')->andReturn($store);
        $stache->shouldReceive('store')->with('another-test-store')->andReturn($anotherStore);

        $duplicates = new Duplicates($stache);

        $this->assertEquals([], $duplicates->all()->all());
        $this->assertEquals(0, $duplicates->count());
        $this->assertTrue($duplicates->isEmpty());
        $this->assertFalse($duplicates->isNotEmpty());

        $duplicates->track($store, '123', 'path/to/duplicate.md');

        // Do it twice to make sure it's only tracked once.
        $duplicates->track($store, '123', 'path/to/duplicate.md');

        // Track another item in the same store
        $duplicates->track($store, '456', 'path/to/another-duplicate.md');

        // Track an item in a different store
        $duplicates->track($anotherStore, '789', 'path/to/yet/another-duplicate.md');

        $this->assertEquals([
            'test-store' => [
                '123' => [
                    'path/to/original.md',
                    'path/to/duplicate.md',
                ],
                '456' => [
                    'path/to/another-original.md',
                    'path/to/another-duplicate.md',
                ],
            ],
            'another-test-store' => [
                '789' => [
                    'path/to/yet/another-original.md',
                    'path/to/yet/another-duplicate.md',
                ],
            ],
        ], $duplicates->all()->all());
        $this->assertEquals(3, $duplicates->count());
        $this->assertFalse($duplicates->isEmpty());
        $this->assertTrue($duplicates->isNotEmpty());

        $duplicates->track($store, '123', 'path/to/triplicate.md');

        $this->assertEquals([
            'test-store' => [
                '123' => [
                    'path/to/original.md',
                    'path/to/duplicate.md',
                    'path/to/triplicate.md',
                ],
                '456' => [
                    'path/to/another-original.md',
                    'path/to/another-duplicate.md',
                ],
            ],
            'another-test-store' => [
                '789' => [
                    'path/to/yet/another-original.md',
                    'path/to/yet/another-duplicate.md',
                ],
            ],
        ], $duplicates->all()->all());
        $this->assertEquals(4, $duplicates->count());
    }

    #[Test]
    public function it_saves_to_the_cache()
    {
        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $stache = $this->mock(Stache::class);
        $stache->shouldReceive('store')->with('test-store')->andReturn($store);
        $stache->shouldReceive('cacheStore')->andReturn(Cache::store());

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

    #[Test]
    public function it_doesnt_save_if_there_are_no_changes()
    {
        $duplicates = new Duplicates($this->mock(Stache::class));

        $this->assertNull(Cache::get('stache::duplicates'));

        $duplicates->cache();

        $this->assertNull(Cache::get('stache::duplicates'));
    }

    #[Test]
    public function it_loads_from_cache()
    {
        Cache::forever('stache::duplicates', ['foo' => 'bar']);

        $stache = $this->mock(Stache::class);
        $stache->shouldReceive('cacheStore')->andReturn(Cache::store());
        $duplicates = new Duplicates($stache);

        $return = $duplicates->load();

        $this->assertEquals($duplicates, $return);
        $this->assertEquals(['foo' => 'bar'], $duplicates->getItems());
    }

    #[Test]
    public function it_clears_the_cache()
    {
        Cache::forever('stache::duplicates', ['foo' => 'bar']);

        $stache = $this->mock(Stache::class);
        $stache->shouldReceive('cacheStore')->andReturn(Cache::store());
        $duplicates = new Duplicates($stache);

        $this->assertNotNull(Cache::get('stache::duplicates'));

        $return = $duplicates->clear();

        $this->assertEquals($duplicates, $return);
        $this->assertNull(Cache::get('stache::duplicates'));
    }

    #[Test]
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

    #[Test]
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
