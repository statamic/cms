<?php

namespace Tests\Stache;

use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Entries\Entry;
use Statamic\Stache\Duplicates;
use Statamic\Stache\Stache;
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
        $duplicates = (new Duplicates($this->mock(Stache::class)))->setItems(['foo' => 'bar']);

        $this->assertNull(Cache::get('stache::duplicates'));

        $duplicates->save();

        $this->assertEquals(['foo' => 'bar'], Cache::get('stache::duplicates'));
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
    public function it_finds_all_the_duplicates()
    {
        $store1 = $this->mock(Store::class)->shouldReceive('paths')->once()->getMock();
        $store2 = $this->mock(Store::class)->shouldReceive('paths')->once()->getMock();

        $stache = $this->mock(Stache::class);
        $stache->shouldReceive('stores')->andReturn(collect([$store1, $store2]));

        $duplicates = (new Duplicates($stache));

        $return = $duplicates->find();

        $this->assertEquals($duplicates, $return);
    }
}
