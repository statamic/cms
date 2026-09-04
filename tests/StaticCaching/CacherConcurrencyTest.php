<?php

namespace Tests\StaticCaching;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\UrlInvalidated;
use Statamic\StaticCaching\Cachers\AbstractCacher;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\StaticCaching\Cachers\FileCacher;
use Statamic\StaticCaching\Cachers\Writer;
use Statamic\StaticCaching\Middleware\Cache as CacheMiddleware;
use Statamic\StaticCaching\NoCache\Session;
use Tests\TestCase;

class CacherConcurrencyTest extends TestCase
{
    public function tearDown(): void
    {
        File::deleteDirectory($this->cachePath());

        parent::tearDown();
    }

    #[Test]
    public function cache_url_is_blocked_while_another_process_holds_the_urls_lock_for_the_domain()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher($cache, ['base_url' => 'http://example.com']);

        $lockKey = 'static-cache:'.md5('http://example.com').'.urls:lock';
        $externalLock = $cache->lock($lockKey, 10);
        $this->assertTrue($externalLock->get());

        try {
            $this->expectException(LockTimeoutException::class);

            $cacher->cacheUrl('one', '/one');
        } finally {
            $externalLock->forceRelease();
        }
    }

    #[Test]
    public function forget_url_and_cache_url_share_the_same_lock_for_a_domain()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher($cache, ['base_url' => 'http://example.com']);

        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one',
        ]);

        $lockKey = 'static-cache:'.md5('http://example.com').'.urls:lock';
        $externalLock = $cache->lock($lockKey, 10);
        $this->assertTrue($externalLock->get());

        try {
            $this->expectException(LockTimeoutException::class);

            $cacher->forgetUrl('one');
        } finally {
            $externalLock->forceRelease();
        }
    }

    #[Test]
    public function cache_domain_has_its_own_independent_lock_from_the_urls_map()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher($cache, ['base_url' => 'http://example.com']);

        $externalLock = $cache->lock('static-cache:domains:lock', 10);
        $this->assertTrue($externalLock->get());

        try {
            $this->expectException(LockTimeoutException::class);

            $cacher->cacheUrl('one', '/one');
        } finally {
            $externalLock->forceRelease();
        }
    }

    #[Test]
    public function it_falls_back_to_unlocked_behavior_when_the_cache_store_does_not_support_locking()
    {
        $store = Mockery::mock(\Illuminate\Contracts\Cache\Store::class);

        $cache = Mockery::mock(\Illuminate\Contracts\Cache\Repository::class);
        $cache->shouldReceive('getStore')->andReturn($store);
        $cache->shouldReceive('get')->andReturn([]);
        $cache->shouldReceive('forever');
        $cache->shouldNotReceive('lock');

        $cacher = $this->cacher($cache, ['base_url' => 'http://example.com']);

        $cacher->cacheUrl('one', '/one');

        $this->assertTrue(true);
    }

    #[Test]
    public function invalidate_urls_only_acquires_the_domain_lock_once_for_a_multi_url_batch()
    {
        $lockKey = 'static-cache:'.md5('http://example.com').'.urls:lock';
        $urlsKey = 'static-cache:'.md5('http://example.com').'.urls';

        $store = Mockery::mock(\Illuminate\Contracts\Cache\LockProvider::class, \Illuminate\Contracts\Cache\Store::class);
        $lock = Mockery::mock(\Illuminate\Contracts\Cache\Lock::class);

        $cache = Mockery::mock(\Illuminate\Contracts\Cache\Repository::class);
        $cache->shouldReceive('getStore')->andReturn($store);
        $cache->shouldReceive('get')->with($urlsKey, [])->andReturn([
            'one' => '/one',
            'two' => '/two',
            'three' => '/three',
        ]);
        // One lock, one map write for the whole batch - not one per URL.
        $cache->shouldReceive('forever')->once()->withArgs(fn ($key) => $key === $urlsKey);

        $cache->shouldReceive('lock')
            ->once()
            ->withArgs(fn ($key) => $key === $lockKey)
            ->andReturn($lock);
        $lock->shouldReceive('block')->once()->andReturnUsing(fn ($seconds, $callback) => $callback());

        $cacher = $this->concreteCacher($cache, ['base_url' => 'http://example.com']);

        $cacher->invalidateUrls(['/one', '/two', '/three']);
    }

    #[Test]
    public function invalidate_urls_throws_once_for_the_whole_batch_when_the_domain_lock_is_contended()
    {
        $cache = app(Repository::class);
        $cacher = $this->concreteCacher($cache, ['base_url' => 'http://example.com']);

        $urlsKey = 'static-cache:'.md5('http://example.com').'.urls';
        $cache->forever($urlsKey, [
            'one' => '/one',
            'two' => '/two',
            'three' => '/three',
        ]);

        $lockKey = 'static-cache:'.md5('http://example.com').'.urls:lock';
        $externalLock = $cache->lock($lockKey, 10);
        $this->assertTrue($externalLock->get());

        try {
            $this->expectException(LockTimeoutException::class);

            $cacher->invalidateUrls(['/one', '/two', '/three']);
        } finally {
            $externalLock->forceRelease();
        }

        // Nothing should have been removed since the batch never acquired the lock.
        $this->assertSame([
            'one' => '/one',
            'two' => '/two',
            'three' => '/three',
        ], $cache->get($urlsKey));
    }

    #[Test]
    public function file_cacher_removes_the_written_file_when_the_urls_lock_is_contended()
    {
        $cache = app(Repository::class);
        $cacher = $this->fileCacher($cache, 'http://example.com');

        $lockKey = 'static-cache:'.md5('http://example.com').'.urls:lock';
        $externalLock = $cache->lock($lockKey, 10);
        $this->assertTrue($externalLock->get());

        try {
            $cacher->cachePage(Request::create('http://example.com/about'), '<html>hello</html>');
        } finally {
            $externalLock->forceRelease();
        }

        // The page couldn't be recorded in the urls map, so the written file
        // must be removed. Otherwise it would be served forever but invisible
        // to invalidation - the exact orphaned state the locking prevents.
        $this->assertFileDoesNotExist($cacher->getFilePath('http://example.com/about'));
        $this->assertNull($cache->get('static-cache:'.md5('http://example.com').'.urls'));
    }

    #[Test]
    public function application_cacher_skips_caching_the_response_when_the_urls_lock_is_contended()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);

        $lockKey = 'static-cache:'.md5('http://example.com').'.urls:lock';
        $externalLock = $cache->lock($lockKey, 10);
        $this->assertTrue($externalLock->get());

        try {
            $cacher->cachePage($request = Request::create('http://example.com/about'), '<html>hello</html>');
        } finally {
            $externalLock->forceRelease();
        }

        // The response listener should never have been registered, so preparing
        // a response must not store the page either.
        Event::dispatch(new ResponsePrepared($request, new Response('<html>hello</html>')));

        $this->assertNull($cache->get('static-cache:responses:'.md5('http://example.com/about')));
        $this->assertNull($cache->get('static-cache:'.md5('http://example.com').'.urls'));
    }

    #[Test]
    public function middleware_serves_the_rendered_response_uncached_when_the_urls_lock_is_contended()
    {
        $cache = app(Repository::class);
        $cacher = $this->fileCacher($cache, 'http://localhost');

        $middleware = new CacheMiddleware($cacher, app(Session::class));

        $lockKey = 'static-cache:'.md5('http://localhost').'.urls:lock';
        $externalLock = $cache->lock($lockKey, 10);
        $this->assertTrue($externalLock->get());

        try {
            $response = $middleware->handle(
                Request::create('http://localhost/about'),
                fn () => new Response('<h1>Hello</h1>')
            );
        } finally {
            $externalLock->forceRelease();
        }

        // The page rendered fine and should reach the visitor, not be discarded
        // in favor of a 503 refresh response just because it couldn't be cached.
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<h1>Hello</h1>', $response->getContent());
        $this->assertFileDoesNotExist($cacher->getFilePath('http://localhost/about'));
    }

    #[Test]
    public function urls_lock_is_released_before_invalidation_cleanup_and_events_run()
    {
        $cache = app(Repository::class);
        $writer = Mockery::spy(Writer::class);

        $cacher = new FileCacher($writer, $cache, [
            'base_url' => 'http://example.com',
            'path' => $this->cachePath(),
            'locale' => 'en',
        ]);

        $cache->forever('static-cache:'.md5('http://example.com').'.urls', ['one' => '/one']);

        $lockKey = 'static-cache:'.md5('http://example.com').'.urls:lock';

        // If invalidation still held the urls lock while dispatching events,
        // this listener wouldn't be able to acquire it - and neither would a
        // visitor request trying to cache a page during a long cleanup.
        $lockWasFree = null;
        Event::listen(UrlInvalidated::class, function () use ($cache, $lockKey, &$lockWasFree) {
            $lock = $cache->lock($lockKey, 10);

            if ($lockWasFree = $lock->get()) {
                $lock->release();
            }
        });

        $cacher->invalidateUrls(['/one']);

        $this->assertTrue($lockWasFree, 'The urls lock should be released before cleanup and events run.');
        $this->assertSame([], $cacher->getUrls('http://example.com')->all());
    }

    #[Test]
    public function file_cacher_flush_wipes_the_urls_map_before_deleting_files()
    {
        $cache = app(Repository::class);
        $urlsKey = 'static-cache:'.md5('http://example.com').'.urls';

        $cache->forever('static-cache:domains', ['http://example.com']);
        $cache->forever($urlsKey, ['one' => '/one']);

        // If files were deleted first, a request re-rendering one of them could
        // write a fresh file whose map entry is then wiped - an orphan. Map
        // first means the worst case is a map entry without a file, which the
        // next request heals by re-caching under the same key.
        $writer = Mockery::mock(Writer::class);
        $writer->shouldReceive('flush')->once()->andReturnUsing(function () use ($cache, $urlsKey) {
            $this->assertNull($cache->get($urlsKey), 'The urls map should be wiped before files are deleted.');
        });

        $cacher = new FileCacher($writer, $cache, [
            'base_url' => 'http://example.com',
            'path' => $this->cachePath(),
            'locale' => 'en',
        ]);

        $cacher->flush();

        $this->assertNull($cache->get($urlsKey));
        $this->assertNull($cache->get('static-cache:domains'));
    }

    #[Test]
    public function application_cacher_flush_wipes_the_urls_map_before_forgetting_responses()
    {
        $urlsKey = 'static-cache:'.md5('http://example.com').'.urls';

        $store = Mockery::mock(\Illuminate\Contracts\Cache\LockProvider::class, \Illuminate\Contracts\Cache\Store::class);
        $lock = Mockery::mock(\Illuminate\Contracts\Cache\Lock::class);
        $lock->shouldReceive('block')->andReturnUsing(fn ($seconds, $callback) => $callback());

        $cache = Mockery::mock(\Illuminate\Contracts\Cache\Repository::class);
        $cache->shouldReceive('getStore')->andReturn($store);
        $cache->shouldReceive('lock')->andReturn($lock);
        $cache->shouldReceive('get')->with('static-cache:domains', [])->andReturn(['http://example.com']);
        $cache->shouldReceive('get')->with($urlsKey, [])->andReturn(['one' => '/one']);

        $cache->shouldReceive('forget')->with($urlsKey)->once()->ordered();
        $cache->shouldReceive('forget')->with('static-cache:domains')->once()->ordered();
        $cache->shouldReceive('forget')->with('static-cache:responses:one')->once()->ordered();

        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);

        $cacher->flush();
    }

    private function cachePath()
    {
        return storage_path('framework/testing/static-cache-concurrency');
    }

    private function fileCacher($cache, $baseUrl)
    {
        return new FileCacher(new Writer, $cache, [
            'base_url' => $baseUrl,
            'path' => $this->cachePath(),
            'locale' => 'en',
        ]);
    }

    private function cacher($cache, $config = [])
    {
        return Mockery::mock(AbstractCacher::class, [$cache, $config])->makePartial();
    }

    /**
     * A minimal concrete AbstractCacher with no driver-side storage, so tests
     * can exercise the shared two-phase invalidation path directly.
     */
    private function concreteCacher($cache, $config = [])
    {
        return new class($cache, $config) extends AbstractCacher
        {
            public function cachePage(Request $request, $content)
            {
            }

            public function getCachedPage(Request $request)
            {
            }

            public function flush()
            {
            }

            protected function cleanupInvalidatedUrls($invalidated, $paths, $domain)
            {
            }
        };
    }
}
