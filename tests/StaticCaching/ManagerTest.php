<?php

namespace Tests\StaticCaching;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[Test]
    public function it_gets_the_current_url()
    {
        $request = Request::create('http://example.com/test', 'GET', [
            'foo' => 'bar',
        ]);

        $this->assertEquals('http://example.com/test?foo=bar', StaticCache::currentUrl($request));
    }

    #[Test]
    public function it_gets_the_current_url_with_query_strings_disabled()
    {
        config()->set('statamic.static_caching.ignore_query_strings', true);

        $request = Request::create('http://example.com/test', 'GET', [
            'foo' => 'bar',
        ]);

        $this->assertEquals('http://example.com/test', StaticCache::currentUrl($request));
    }

    #[Test]
    public function it_gets_the_current_url_with_allowed_query_parameters()
    {
        config()->set('statamic.static_caching.allowed_query_strings', [
            'foo', 'quux',
        ]);

        $request = Request::create('http://example.com/test', 'GET', [
            'foo' => 'bar',
            'baz' => 'qux',
            'quux' => 'corge',
        ]);

        $this->assertEquals('http://example.com/test?foo=bar&quux=corge', StaticCache::currentUrl($request));
    }

    #[Test]
    #[DataProvider('disallowedQueryParametersProvider')]
    public function it_gets_the_current_url_with_disallowed_query_parameters(array $disallowed, string $url, array $query, string $expected)
    {
        config()->set('statamic.static_caching.disallowed_query_strings', $disallowed);

        $request = Request::create($url, 'GET', $query);

        $this->assertEquals($expected, StaticCache::currentUrl($request));
    }

    public static function disallowedQueryParametersProvider()
    {
        return [
            [[], 'http://example.com/test', ['foo' => 'bar'], 'http://example.com/test?foo=bar'],
            [['quux'], 'http://example.com/test', ['quux' => 'corge'], 'http://example.com/test'],
            [['quux'], 'http://example.com/test', ['foo' => 'bar', 'baz' => 'qux', 'quux' => 'corge'], 'http://example.com/test?baz=qux&foo=bar'],
        ];
    }
}
