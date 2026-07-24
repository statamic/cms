<?php

namespace Tests\StaticCaching;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\Cachers\AbstractCacher;
use Tests\TestCase;

class CacherConcurrencyTest extends TestCase
{
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
        $cache->shouldReceive('forever')->withArgs(fn ($key) => $key === $urlsKey);

        // The whole point of the fix: one lock covers the entire batch, not
        // one acquisition per URL.
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

    private function cacher($cache, $config = [])
    {
        return Mockery::mock(AbstractCacher::class, [$cache, $config])->makePartial();
    }

    /**
     * A minimal concrete AbstractCacher whose invalidateUrl() mirrors the real
     * FileCacher/ApplicationCacher pattern of looking up matching urls map
     * entries and calling forgetUrl() per match, so tests can exercise the
     * real nested-lock path (invalidateUrls() -> invalidateUrl() -> forgetUrl()).
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

            public function invalidateUrl($url, $domain = null)
            {
                $domain = $domain ?? $this->getBaseUrl();

                $this->getUrls($domain)
                    ->filter(fn ($value) => $value === $url)
                    ->each(function ($value, $key) use ($domain) {
                        $this->forgetUrl($key, $domain);
                    });
            }
        };
    }
}
