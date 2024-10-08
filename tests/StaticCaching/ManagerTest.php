<?php

namespace Tests\StaticCaching;

use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\StaticCache;
use Statamic\StaticCaching\Cacher;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    #[Test]
    public function it_flushes()
    {
        config([
            'statamic.static_caching.strategy' => 'test',
            'statamic.static_caching.strategies.test.driver' => 'test',
            'cache.stores.static_cache' => [
                'driver' => 'file',
                'path' => storage_path('statamic/static-urls-cache'),
            ],
        ]);

        $mock = Mockery::mock(Cacher::class)->shouldReceive('flush')->once()->getMock();
        StaticCache::extend('test', fn () => $mock);

        Cache::shouldReceive('store')->andReturnSelf();
        Cache::shouldReceive('flush')->once();

        StaticCache::flush();
    }

    #[Test]
    public function it_flushes_without_static_cache_store()
    {
        config([
            'statamic.static_caching.strategy' => 'test',
            'statamic.static_caching.strategies.test.driver' => 'test',
        ]);

        $mock = Mockery::mock(Cacher::class)->shouldReceive('flush')->once()->getMock();
        StaticCache::extend('test', fn () => $mock);

        Cache::shouldReceive('store')->andReturnSelf();
        Cache::shouldReceive('get')->with('nocache::urls', [])->once()->andReturn(['/one', '/two']);
        Cache::shouldReceive('get')->with('nocache::session.'.md5('/one'))->once()->andReturn(['regions' => ['r1', 'r2']]);
        Cache::shouldReceive('get')->with('nocache::session.'.md5('/two'))->once()->andReturn(['regions' => ['r3', 'r4']]);
        Cache::shouldReceive('forget')->with('nocache::region.r1')->once();
        Cache::shouldReceive('forget')->with('nocache::region.r2')->once();
        Cache::shouldReceive('forget')->with('nocache::region.r3')->once();
        Cache::shouldReceive('forget')->with('nocache::region.r4')->once();
        Cache::shouldReceive('forget')->with('nocache::session.'.md5('/one'))->once();
        Cache::shouldReceive('forget')->with('nocache::session.'.md5('/two'))->once();
        Cache::shouldReceive('forget')->with('nocache::urls')->once();

        StaticCache::flush();
    }
}
