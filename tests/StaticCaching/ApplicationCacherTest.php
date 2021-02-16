<?php

namespace Tests\StaticCaching;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Tests\TestCase;

class ApplicationCacherTest extends TestCase
{
    /** @test */
    public function it_checks_if_a_page_is_cached()
    {
        $key = 'static-cache:responses:'.md5('http://example.com/test?foo=bar');
        $cache = $this->mock(Repository::class);
        $cache->shouldReceive('get')->with($key)->times(2)->andReturn(null, 'html content');
        $cache->shouldNotReceive('has');

        $cacher = new ApplicationCacher($cache, []);
        $request = Request::create('http://example.com/test', 'GET', ['foo' => 'bar']);

        $this->assertFalse($cacher->hasCachedPage($request));
        $this->assertTrue($cacher->hasCachedPage($request));
    }

    /** @test */
    public function gets_cached_page()
    {
        $key = 'static-cache:responses:'.md5('http://example.com/test?foo=bar');
        $cache = $this->mock(Repository::class);
        $cache->shouldReceive('get')->with($key)->once()->andReturn('html content');

        $cacher = new ApplicationCacher($cache, []);
        $request = Request::create('http://example.com/test', 'GET', ['foo' => 'bar']);

        $this->assertEquals('html content', $cacher->getCachedPage($request));
    }

    /** @test */
    public function checking_if_page_is_cached_then_retrieving_it_will_only_hit_the_cache_once()
    {
        $key = 'static-cache:responses:'.md5('http://example.com/test?foo=bar');
        $cache = $this->mock(Repository::class);
        $cache->shouldReceive('get')->with($key)->once()->andReturn('html content');
        $cache->shouldNotReceive('has');

        $cacher = new ApplicationCacher($cache, []);
        $request = Request::create('http://example.com/test', 'GET', ['foo' => 'bar']);

        $this->assertTrue($cacher->hasCachedPage($request));
        $this->assertEquals('html content', $cacher->getCachedPage($request));
    }

    /** @test */
    public function invalidating_a_url_removes_the_html_and_the_url()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one', 'two' => '/two',
        ]);
        $cache->forever('static-cache:responses:one', 'html content');

        $cacher->invalidateUrl('/one');

        $this->assertEquals(['two' => '/two'], $cacher->getUrls()->all());
        $this->assertNull($cache->get('static-cache:responses:one'));
    }
}
