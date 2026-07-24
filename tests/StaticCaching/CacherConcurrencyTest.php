<?php

namespace Tests\StaticCaching;

use Illuminate\Cache\Repository;
use Illuminate\Contracts\Cache\LockTimeoutException;
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

    private function cacher($cache, $config = [])
    {
        return Mockery::mock(AbstractCacher::class, [$cache, $config])->makePartial();
    }
}
