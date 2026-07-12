<?php

namespace Tests\Stache\Stores;

use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Stache\Exceptions\DuplicateKeyException;
use Statamic\Stache\Stores\Keys;
use Statamic\Stache\Stores\Store;
use Tests\TestCase;

class KeysTest extends TestCase
{
    #[Test]
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

    #[Test]
    public function adding_a_duplicate_throws_an_exception()
    {
        $keys = (new Keys($this->mock(Store::class)))->setKeys(['123' => 'original.md']);

        $keys->add('123', 'original.md'); // adding the same one twice is ok if it's the same path.
        $keys->add('456', 'another.md');

        try {
            $keys->add('123', 'duplicate.md');
        } catch (DuplicateKeyException $e) {
            $this->assertEquals('123', $e->getKey());
            $this->assertEquals('duplicate.md', $e->getPath());
            $this->assertEquals([
                '123' => 'original.md',
                '456' => 'another.md',
            ], $keys->all());

            return;
        }

        $this->fail('Exception was not thrown.');
    }

    #[Test]
    public function it_saves_to_the_cache()
    {
        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = (new Keys($store))->setKeys(['foo' => 'bar']);

        $this->assertNull(Cache::get('stache::keys/test-store'));

        $keys->cache();

        $this->assertEquals(['foo' => 'bar'], Cache::get('stache::keys/test-store'));
    }

    #[Test]
    public function it_can_use_a_different_cache_store()
    {
        config()->set('statamic.stache.cache_store', 'stache');
        config()->set('cache.stores.stache', [
            'driver' => 'array',
        ]);

        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = (new Keys($store))->setKeys(['foo' => 'bar']);

        $this->assertNull(Cache::get('stache::keys/test-store'));
        $this->assertNull(Cache::store('stache')->get('stache::keys/test-store'));

        $keys->cache();

        $this->assertNull(Cache::get('stache::keys/test-store'));
        $this->assertEquals(['foo' => 'bar'], Cache::store('stache')->get('stache::keys/test-store'));
    }

    #[Test]
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

    #[Test]
    public function it_clears_the_cache()
    {
        Cache::forever('stache::keys/test-store', ['foo' => 'bar']);

        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = new Keys($store);

        $this->assertNotNull(Cache::get('stache::keys/test-store'));

        $keys->clear();

        $this->assertNull(Cache::get('stache::keys/test-store'));
    }

    #[Test]
    public function it_forgets_a_key()
    {
        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = (new Keys($store))->setKeys([
            '123' => 'original.md',
            '456' => 'another.md',
        ]);

        $return = $keys->forget('123');

        $this->assertEquals($keys, $return);
        $this->assertEquals(['456' => 'another.md'], $keys->all());
    }

    #[Test]
    public function it_sets_the_path_of_a_key()
    {
        $store = $this->mock(Store::class);
        $store->shouldReceive('key')->andReturn('test-store');

        $keys = (new Keys($store))->setKeys([
            '123' => 'original.md',
            '456' => 'another.md',
        ]);

        $return = $keys->set('123', 'changed.md');

        $this->assertEquals($keys, $return);
        $this->assertEquals([
            '123' => 'changed.md',
            '456' => 'another.md',
        ], $keys->all());
    }
}
