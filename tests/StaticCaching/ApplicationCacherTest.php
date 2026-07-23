<?php

namespace Tests\StaticCaching;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Console\Commands\StaticWarmJob;
use Statamic\Events\UrlInvalidated;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Tests\TestCase;

class ApplicationCacherTest extends TestCase
{
    #[Test]
    public function it_checks_if_a_page_is_cached()
    {
        $key = 'static-cache:responses:'.md5('http://example.com/test?foo=bar');
        $cache = $this->mock(Repository::class);
        $cache->shouldReceive('get')->with($key)->times(2)->andReturn(null, ['content' => 'html content', 'headers' => []]);
        $cache->shouldNotReceive('has');

        $cacher = new ApplicationCacher($cache, []);
        $request = Request::create('http://example.com/test', 'GET', ['foo' => 'bar']);

        $this->assertFalse($cacher->hasCachedPage($request));
        $this->assertTrue($cacher->hasCachedPage($request));
    }

    #[Test]
    public function gets_cached_page()
    {
        $key = 'static-cache:responses:'.md5('http://example.com/test?foo=bar');
        $cache = $this->mock(Repository::class);
        $cache->shouldReceive('get')->with($key)->once()->andReturn(['content' => 'html content', 'headers' => [
            'Content-Type' => 'application/html',
        ]]);

        $cacher = new ApplicationCacher($cache, []);
        $request = Request::create('http://example.com/test', 'GET', ['foo' => 'bar']);

        $cachedPage = $cacher->getCachedPage($request);
        $this->assertEquals('html content', $cachedPage->content);
        $this->assertEquals('application/html', $cachedPage->headers['Content-Type']);
        $this->assertEquals(200, $cachedPage->status);
    }

    #[Test]
    public function checking_if_page_is_cached_then_retrieving_it_will_only_hit_the_cache_once()
    {
        $key = 'static-cache:responses:'.md5('http://example.com/test?foo=bar');
        $cache = $this->mock(Repository::class);
        $cache->shouldReceive('get')->with($key)->once()->andReturn(['content' => 'html content', 'headers' => [
            'Content-Type' => 'application/html',
        ]]);
        $cache->shouldNotReceive('has');

        $cacher = new ApplicationCacher($cache, []);
        $request = Request::create('http://example.com/test', 'GET', ['foo' => 'bar']);

        $this->assertTrue($cacher->hasCachedPage($request));

        $cachedPage = $cacher->getCachedPage($request);
        $this->assertEquals('html content', $cachedPage->content);
        $this->assertEquals('application/html', $cachedPage->headers['Content-Type']);
    }

    #[Test]
    public function caching_a_successful_page_tracks_the_url()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $request = Request::create('http://example.com/about', 'GET');

        $cacher->cachePage($request, response('about page', 200));
        event(new ResponsePrepared($request, response('about page', 200)));

        $this->assertEquals(['/about'], $cacher->getUrls()->values()->all());
    }

    #[Test]
    public function caching_a_404_page_does_not_track_the_url()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $request = Request::create('http://example.com/this-does-not-exist', 'GET');
        $response = response('not found', 404);

        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));

        // The URL isn't tracked in the `.urls` set...
        $this->assertEquals([], $cacher->getUrls()->all());

        // ...but the 404 response is still cached and served directly by URL hash.
        $this->assertTrue($cacher->hasCachedPage($request));
        $this->assertEquals('not found', $cacher->getCachedPage($request)->content);
    }

    #[Test]
    public function caching_a_shared_error_page_is_always_tracked()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $request = Request::createFrom(Request::create('http://example.com/'))->fakeStaticCacheStatus(404);
        $response = response('shared not found', 404);

        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));

        $this->assertEquals(['/__shared-errors/'.\Statamic\Facades\Site::current()->handle().'/404'], $cacher->getUrls()->values()->all());
    }

    #[Test]
    public function wildcard_refresh_does_not_warm_untracked_404_urls()
    {
        Queue::fake();

        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);

        $goodRequest = Request::create('http://example.com/rail/one', 'GET');
        $cacher->cachePage($goodRequest, response('one', 200));
        event(new ResponsePrepared($goodRequest, response('one', 200)));

        $junkRequest = Request::create('http://example.com/rail/scanner-junk', 'GET');
        $cacher->cachePage($junkRequest, response('not found', 404));
        event(new ResponsePrepared($junkRequest, response('not found', 404)));

        $cacher->refreshUrls(['/rail/*']);

        Queue::assertPushed(StaticWarmJob::class, function ($job) {
            return str_contains((string) $job->request->getUri(), '/rail/one');
        });
        Queue::assertNotPushed(StaticWarmJob::class, function ($job) {
            return str_contains((string) $job->request->getUri(), 'scanner-junk');
        });
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function invalidating_a_url_with_explicit_domain_updates_correct_url_index()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);

        // Different domain than base_url
        $domainHash = md5('http://differentexample.com');
        $cache->forever("static-cache:{$domainHash}.urls", [
            'one' => '/one',
            'two' => '/two',
        ]);
        $cache->forever('static-cache:responses:one', 'html content');
        $cache->forever('static-cache:responses:two', 'two html content');

        // Invalidate explicit domain
        $cacher->invalidateUrl('/one', 'http://differentexample.com');

        // URL index under http://differentexample.com should be updated and not http://example.com
        $this->assertEquals(
            ['two' => '/two'],
            $cacher->getUrls('http://differentexample.com')->all()
        );
        $this->assertNull($cache->get('static-cache:responses:one'));
        $this->assertNotNull($cache->get('static-cache:responses:two'));
    }

    #[Test]
    public function invalidating_a_full_url_without_domain_extracts_domain_correctly()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);

        // Different domain than base_url
        $domainHash = md5('http://differentexample.com');
        $cache->forever("static-cache:{$domainHash}.urls", [
            'one' => '/one',
            'two' => '/two',
        ]);
        $cache->forever('static-cache:responses:one', 'html content');
        $cache->forever('static-cache:responses:two', 'two html content');

        // Invalidate full URL with no explicit domain to simulate CLI
        $cacher->invalidateUrl('http://differentexample.com/one');

        // Should extract domain from URL and update correct URL index
        $this->assertEquals(
            ['two' => '/two'],
            $cacher->getUrls('http://differentexample.com')->all()
        );
        $this->assertNull($cache->get('static-cache:responses:one'));
        $this->assertNotNull($cache->get('static-cache:responses:two'));
    }

    #[Test]
    #[DataProvider('invalidateEventProvider')]
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

    #[Test]
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

    #[Test]
    #[DataProvider('currentUrlProvider')]
    public function it_gets_the_current_url(
        array $query,
        array $config,
        string $expectedUrl
    ) {
        $request = Request::create('http://example.com/test', 'GET', $query);

        $cacher = new ApplicationCacher(app(Repository::class), $config);

        $this->assertEquals($expectedUrl, $cacher->getUrl($request));
    }

    public static function currentUrlProvider()
    {
        return [
            'no query' => [
                [],
                [],
                'http://example.com/test',
            ],
            'with query' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                [],
                'http://example.com/test?alfa=a&bravo=b&charlie=c',
            ],
            'with query, ignoring query' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                ['ignore_query_strings' => true],
                'http://example.com/test',
            ],
            'with query, allowed query' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                ['allowed_query_strings' => ['alfa', 'bravo']],
                'http://example.com/test?alfa=a&bravo=b',
            ],
            'with query, disallowed query' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                ['disallowed_query_strings' => ['charlie']],
                'http://example.com/test?alfa=a&bravo=b',
            ],
            'with query, allowed and disallowed' => [
                ['bravo' => 'b', 'charlie' => 'c', 'alfa' => 'a'],
                [
                    'allowed_query_strings' => ['alfa', 'bravo'],
                    'disallowed_query_strings' => ['bravo'],
                ],
                'http://example.com/test?alfa=a',
            ],
        ];
    }
}
