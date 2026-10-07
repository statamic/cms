<?php

namespace Tests\Stache;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Stache;
use Statamic\Stache\Stores\Store;
use Tests\Fakes\FakeArtisanRequest;
use Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_resets_memoized_store_and_index_state_between_jobs_when_running_in_a_worker()
    {
        $store = (new ServiceProviderTestStore)->directory('/path/to/directory');
        Stache::registerStore($store);

        $pathsCacheKey = "stache::indexes::{$store->key()}::path";
        $idIndexCacheKey = "stache::indexes::{$store->key()}::id";

        Cache::put($pathsCacheKey, ['foo', 'bar']);
        Cache::put($idIndexCacheKey, ['alfa' => 'alfa.md']);

        $hits = collect();
        Event::listen(CacheHit::class, function ($event) use (&$hits) {
            $hits->push($event->key);
        });

        Request::swap(new FakeArtisanRequest('queue:work'));

        // Memoize both the store's paths and one of its indexes.
        $store->paths();
        $store->index('id');
        $store->paths();
        $store->index('id');
        $this->assertEquals(1, $hits->filter(fn ($key) => $key === $pathsCacheKey)->count());
        $this->assertEquals(1, $hits->filter(fn ($key) => $key === $idIndexCacheKey)->count());

        // Simulate the worker's daemon loop moving on to its next job. This is what
        // actually fires the `JobProcessing` listener registered by the Stache
        // service provider, as opposed to calling `resetMemoizedState()` directly.
        Event::dispatch(new JobProcessing('sync', $this->fakeJob()));

        // Both should be re-fetched from the cache store now that the job boundary has passed.
        $store->paths();
        $store->index('id');
        $this->assertEquals(2, $hits->filter(fn ($key) => $key === $pathsCacheKey)->count());
        $this->assertEquals(2, $hits->filter(fn ($key) => $key === $idIndexCacheKey)->count());
    }

    #[Test]
    public function it_does_not_reset_memoized_state_for_jobs_processed_outside_a_worker()
    {
        // `Illuminate\Queue\SyncQueue` also fires `JobProcessing` for jobs dispatched
        // synchronously during an ordinary web request, so the listener must stay a
        // no-op outside of an actual `queue:*`/`horizon:*` worker process.
        $store = (new ServiceProviderTestStore)->directory('/path/to/directory');
        Stache::registerStore($store);

        $pathsCacheKey = "stache::indexes::{$store->key()}::path";

        Cache::put($pathsCacheKey, ['foo', 'bar']);

        $hits = 0;
        Event::listen(CacheHit::class, function ($event) use (&$hits, $pathsCacheKey) {
            if ($event->key === $pathsCacheKey) {
                $hits++;
            }
        });

        $store->paths();
        $this->assertEquals(1, $hits);

        Event::dispatch(new JobProcessing('sync', $this->fakeJob()));

        $store->paths();
        $this->assertEquals(1, $hits);
    }

    private function fakeJob(): Job
    {
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('payload')->andReturn([]);

        return $job;
    }
}

class ServiceProviderTestStore extends Store
{
    public function getItem($key)
    {
    }

    public function getItemValues($keys, $valueIndex, $keyIndex)
    {
    }

    public function key()
    {
        return 'service-provider-test-store';
    }
}
