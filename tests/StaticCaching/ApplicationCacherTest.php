<?php

namespace Tests\StaticCaching;

use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Repository as CacheRepository;
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
    #[DataProvider('cachedResponseProvider')]
    public function caching_a_page_tracks_the_url($status, $content)
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $request = Request::create('http://example.com/about', 'GET');
        $response = response($content, $status);

        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));

        $this->assertEquals(['/about'], $cacher->getUrls()->values()->all());
        $this->assertTrue($cacher->hasCachedPage($request));
        $this->assertEquals($content, $cacher->getCachedPage($request)->content);
    }

    public static function cachedResponseProvider()
    {
        return [
            'successful response' => [200, 'about page'],
            'error response' => [404, 'not found'],
        ];
    }

    #[Test]
    public function refreshing_a_wildcard_warms_successful_urls_and_invalidates_error_urls()
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

        // The error response is invalidated rather than warmed, so the
        // tracked set converges to real pages.
        $this->assertEquals(['/rail/one'], $cacher->getUrls()->values()->all());
        $this->assertFalse($cacher->hasCachedPage($junkRequest));
    }

    #[Test]
    public function refreshing_an_error_url_invalidates_it_instead_of_warming_it()
    {
        Queue::fake();

        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $request = Request::create('http://example.com/foo', 'GET');
        $response = response('not found', 404);

        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));

        $cacher->refreshUrls(['/foo']);

        Queue::assertNothingPushed();
        $this->assertEquals([], $cacher->getUrls()->all());
        $this->assertFalse($cacher->hasCachedPage($request));
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
    public function invalidating_a_url_removes_a_cached_error_response()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $request = Request::create('http://example.com/foo', 'GET');
        $response = response('not found', 404);

        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));

        $cacher->invalidateUrl('/foo');

        $this->assertEquals([], $cacher->getUrls()->all());
        $this->assertFalse($cacher->hasCachedPage($request));
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
    public function invalidating_multiple_urls_removes_the_html_and_the_urls()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one',
            'oneqs' => '/one?foo=bar',
            'onemore' => '/onemore',
            'two' => '/two',
            'three' => '/three',
        ]);
        $cache->forever('static-cache:'.md5('http://another.com').'.urls', [
            'one' => '/one',
            'two' => '/two',
        ]);
        foreach (['one', 'oneqs', 'onemore', 'two', 'three'] as $key) {
            $cache->forever('static-cache:responses:'.$key, $key.' html content');
        }

        $cacher->invalidateUrls(['/one', 'two', 'http://another.com/two', '/one', '/not-cached']);

        $this->assertEquals([
            'onemore' => '/onemore',
            'three' => '/three',
        ], $cacher->getUrls()->all());
        $this->assertEquals(['one' => '/one'], $cacher->getUrls('http://another.com')->all());
        $this->assertNull($cache->get('static-cache:responses:one'));
        $this->assertNull($cache->get('static-cache:responses:oneqs'));
        $this->assertNull($cache->get('static-cache:responses:two'));
        $this->assertNotNull($cache->get('static-cache:responses:onemore'));
        $this->assertNotNull($cache->get('static-cache:responses:three'));
    }

    #[Test]
    public function invalidating_multiple_urls_supports_wildcards()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'blog' => '/blog',
            'one' => '/blog/one',
            'two' => '/blog/two?page=2',
            'about' => '/about',
            'contact' => '/contact',
        ]);

        $cacher->invalidateUrls(['/blog/*', '/contact']);

        $this->assertEquals([
            'blog' => '/blog',
            'about' => '/about',
        ], $cacher->getUrls()->all());
    }

    #[Test]
    public function invalidating_multiple_urls_rewrites_each_domains_url_index_once()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $urls = collect(range(1, 50))->mapWithKeys(fn ($i) => ["key{$i}" => "/page/{$i}"])->all();
        $cache->forever('static-cache:'.md5('http://example.com').'.urls', $urls);
        $cache->forever('static-cache:'.md5('http://another.com').'.urls', $urls);

        $writes = [];
        Event::listen(KeyWritten::class, function ($event) use (&$writes) {
            $writes[] = $event->key;
        });

        $cacher->invalidateUrls([
            ...collect(range(1, 40))->map(fn ($i) => "/page/{$i}")->all(),
            '/page/4*',
            'http://another.com/page/1',
            'http://another.com/page/2',
        ]);

        $this->assertEquals([
            'static-cache:'.md5('http://example.com').'.urls',
            'static-cache:'.md5('http://another.com').'.urls',
        ], $writes);
        $this->assertEquals(['key50' => '/page/50'], $cacher->getUrls()->all());
        $this->assertCount(48, $cacher->getUrls('http://another.com'));
    }

    #[Test]
    public function invalidating_multiple_urls_dispatches_an_event_for_each_url()
    {
        Event::fake([UrlInvalidated::class]);

        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://base.com']);
        $this->instance(Cacher::class, $cacher);
        $cache->forever('static-cache:'.md5('http://base.com').'.urls', [
            'one' => '/blog/one',
            'two' => '/blog/two',
        ]);

        $cacher->invalidateUrls(['/foo', 'http://another.com/bar', '/blog/*']);

        Event::assertDispatchedTimes(UrlInvalidated::class, 4);
        foreach (['http://base.com/foo', 'http://another.com/bar', 'http://base.com/blog/one', 'http://base.com/blog/two'] as $url) {
            Event::assertDispatched(UrlInvalidated::class, fn ($event) => $event->url === $url);
        }
    }

    #[Test]
    public function invalidating_multiple_urls_matches_invalidating_them_one_at_a_time()
    {
        $urls = [
            'home' => '/',
            'a' => '/product/a',
            'aqs' => '/product/a?ref=1',
            'ab' => '/product/ab',
            'b' => '/product/b',
            'c' => '/category/books?page=2&sort=asc',
            'cqs' => '/category/books?page=2&sort=asc?x',
            'd' => '/category/books',
        ];
        $invalidate = ['/product/a', '/category/books?page=2&sort=asc', '/not-cached', 'http://other.com/product/b', '/categ*'];

        $batched = new ApplicationCacher($batchedCache = new CacheRepository(new ArrayStore), ['base_url' => 'http://example.com']);
        $single = new ApplicationCacher($singleCache = new CacheRepository(new ArrayStore), ['base_url' => 'http://example.com']);

        foreach ([$batchedCache, $singleCache] as $cache) {
            $cache->forever('static-cache:'.md5('http://example.com').'.urls', $urls);
            foreach (array_keys($urls) as $key) {
                $cache->forever('static-cache:responses:'.$key, 'html content');
            }
        }

        $batched->invalidateUrls($invalidate);
        collect($invalidate)->each(fn ($url) => str_contains($url, '*')
            ? (fn () => $this->invalidateWildcardUrl($url))->call($single)
            : $single->invalidateUrl($url));

        $this->assertEquals($single->getUrls()->all(), $batched->getUrls()->all());
        foreach (array_keys($urls) as $key) {
            $this->assertSame($singleCache->has('static-cache:responses:'.$key), $batchedCache->has('static-cache:responses:'.$key), $key);
        }
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
    public function flushing_removes_cached_error_responses()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $request = Request::create('http://example.com/foo', 'GET');
        $response = response('not found', 404);

        $cacher->cachePage($request, $response);
        event(new ResponsePrepared($request, $response));

        $cacher->flush();

        $this->assertEquals([], $cacher->getUrls()->all());
        $this->assertFalse($cacher->hasCachedPage($request));
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
