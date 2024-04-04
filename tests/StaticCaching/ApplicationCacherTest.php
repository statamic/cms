<?php

namespace Tests\StaticCaching;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Statamic\Events\UrlInvalidated;
use Statamic\StaticCaching\Cacher;
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
            'one' => '/one',
            'onemore' => '/onemore',
            'two' => '/two',
        ]);
        $cache->forever('static-cache:responses:one', 'html content');
        $cache->forever('static-cache:responses:onemore', 'onemore html content');
        $cache->forever('static-cache:responses:two', 'two html content');

        $cacher->invalidateUrl('/one');

        $this->assertEquals([
            'onemore' => '/onemore',
            'two' => '/two',
        ], $cacher->getUrls()->all());
        $this->assertNull($cache->get('static-cache:responses:one'));
        $this->assertNotNull($cache->get('static-cache:responses:onemore'));
        $this->assertNotNull($cache->get('static-cache:responses:two'));
    }

    /** @test */
    public function invalidating_a_url_will_invalidate_all_query_string_versions_too()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one',
            'oneqs' => '/one?foo=bar',
            'onemore' => '/onemore',
            'two' => '/two',
        ]);
        $cache->forever('static-cache:responses:one', 'html content');
        $cache->forever('static-cache:responses:oneqs', 'querystring html content');
        $cache->forever('static-cache:responses:onemore', 'onemore html content');
        $cache->forever('static-cache:responses:two', 'two html content');

        $cacher->invalidateUrl('/one');

        $this->assertEquals([
            'two' => '/two',
            'onemore' => '/onemore',
        ], $cacher->getUrls()->all());
        $this->assertNull($cache->get('static-cache:responses:one'));
        $this->assertNull($cache->get('static-cache:responses:oneqs'));
        $this->assertNotNull($cache->get('static-cache:responses:onemore'));
        $this->assertNotNull($cache->get('static-cache:responses:two'));
    }

    /**
     * @test
     *
     * @dataProvider invalidateEventProvider
     */
    public function invalidating_a_url_dispatches_event($domain, $expectedUrl)
    {
        Event::fake();

        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://base.com']);

        // Put it in the container so that the event can resolve it.
        $this->instance(Cacher::class, $cacher);

        $cacher->invalidateUrl('/foo', $domain);

        Event::assertDispatched(UrlInvalidated::class, function ($event) use ($expectedUrl) {
            return $event->url === $expectedUrl;
        });
    }

    public static function invalidateEventProvider()
    {
        return [
            'no domain' => [null, 'http://base.com/foo'],
            'configured base domain' => ['http://base.com', 'http://base.com/foo'],
            'another domain' => ['http://another.com', 'http://another.com/foo'],
        ];
    }

    /** @test */
    public function it_flushes()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $cache->forever('static-cache:domains', [
            'http://example.com',
            'http://another.com',
        ]);
        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one', 'two' => '/two',
        ]);
        $cache->forever('static-cache:'.md5('http://another.com').'.urls', [
            'three' => '/three', 'four' => '/four',
        ]);
        $cache->forever('static-cache:responses:one', 'html content');
        $cache->forever('static-cache:responses:two', 'html content');
        $cache->forever('static-cache:responses:three', 'html content');
        $cache->forever('static-cache:responses:four', 'html content');

        $cacher->flush();

        $this->assertNull($cache->get('static-cache:responses:one'));
        $this->assertNull($cache->get('static-cache:responses:two'));
        $this->assertNull($cache->get('static-cache:responses:three'));
        $this->assertNull($cache->get('static-cache:responses:four'));
        $this->assertEquals([], $cacher->getUrls('http://example.com')->all());
        $this->assertEquals([], $cacher->getUrls('http://another.com')->all());
    }
}
