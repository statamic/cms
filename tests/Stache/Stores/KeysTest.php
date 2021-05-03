<?php

namespace Tests\Stache\Stores;

use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Stores\Keys;
use Statamic\Stache\Stores\Store;
use Tests\TestCase;

class KeysTest extends TestCase
{
    /** @test */
    public function it_tracks_keys()
    {
        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = new Keys($store);

        $this->assertEquals([], $keys->all());

        $keys->add('123', 'path/to/item.md');

        $this->assertEquals([
            '123' => 'path/to/item.md',
        ], $keys->all());
    }

    /** @test */
    public function it_saves_to_the_cache()
    {
        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = (new Keys($store))->setKeys(['foo' => 'bar']);

        $this->assertNull(Cache::get('stache::keys/test-store'));

        $keys->save();

        $this->assertEquals(['foo' => 'bar'], Cache::get('stache::keys/test-store'));
    }

    /** @test */
    public function it_loads_from_cache()
    {
        Cache::forever('stache::keys/test-store', ['foo' => 'bar']);

        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = new Keys($store);

        $return = $keys->load();

        $this->assertEquals($keys, $return);
        $this->assertEquals(['foo' => 'bar'], $keys->all());
    }

    /** @test */
    public function it_checks_if_a_duplicate_exists()
    {
        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = (new Keys($store))->setKeys([
            '123' => 'path/to/item.md',
        ]);

        $this->assertFalse($keys->isDuplicate('123', 'path/to/item.md'));
        $this->assertTrue($keys->isDuplicate('123', 'another/path.md'));
        $this->assertFalse($keys->isDuplicate('another-id', 'another/path.md'));
    }
}
