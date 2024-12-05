<?php

namespace Tests\Stache;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Stache\Stache;
use Statamic\Stache\Stores\Store;
use Tests\Fakes\FakeArtisanRequest;
use Tests\TestCase;

class StoreTest extends TestCase
{
    private $store;

    public function setUp(): void
    {
        parent::setUp();

        $stache = new Stache;

        $this->store = new TestStore($stache);
    }

    #[Test]
    public function it_forces_a_trailing_slash_when_setting_the_directory()
    {
        $this->assertNull($this->store->directory());

        $return = $this->store->directory('/path/to/directory');

        $this->assertEquals($this->store, $return);
        $this->assertEquals('/path/to/directory/', $this->store->directory());

        // Check the value of the property to make sure the property was set with
        // the slash, and that ->directory() isn't just appending it.
        $property = (new \ReflectionClass($this->store))->getProperty('directory');
        $property->setAccessible(true);
        $this->assertEquals('/path/to/directory/', $property->getValue($this->store));
    }

    #[Test]
    public function it_gets_the_paths_from_the_cache_only_once()
    {
        $store = $this->store->directory('/path/to/directory');
        $cacheKey = "stache::indexes::{$store->key()}::path";

        Cache::put($cacheKey, ['foo', 'bar']);

        $cacheHits = 0;
        Event::listen(CacheHit::class, function ($event) use (&$cacheHits, $cacheKey) {
            if ($event->key === $cacheKey) {
                $cacheHits++;
            }
        });

        $expected = collect(['foo', 'bar']);
        $this->assertEquals($expected, $store->paths());
        $this->assertEquals(1, $cacheHits);
        $this->assertEquals($expected, $store->paths());
        $this->assertEquals(1, $cacheHits);
    }

    #[Test]
    public function it_gets_the_paths_from_the_cache_every_time_if_running_in_a_queue_worker()
    {
        $store = $this->store->directory('/path/to/directory');
        $cacheKey = "stache::indexes::{$store->key()}::path";

        Cache::put($cacheKey, ['foo', 'bar']);

        $cacheHits = 0;
        Event::listen(CacheHit::class, function ($event) use (&$cacheHits, $cacheKey) {
            if ($event->key === $cacheKey) {
                $cacheHits++;
            }
        });

        Request::swap(new FakeArtisanRequest('queue:listen'));

        $expected = collect(['foo', 'bar']);
        $this->assertEquals($expected, $store->paths());
        $this->assertEquals(1, $cacheHits);
        $this->assertEquals($expected, $store->paths());
        $this->assertEquals(2, $cacheHits);
    }
}

class TestStore extends Store
{
    public function getItem($key)
    {
    }

    public function getItemValues($keys, $valueIndex, $keyIndex)
    {
    }

    public function key()
    {
        return 'test-store';
    }
}
