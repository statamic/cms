<?php

namespace Tests\StaticCaching;

use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\StaticCaching\Cachers\AbstractCacher;
use Tests\TestCase;

class CacherTest extends TestCase
{
    /** @test */
    public function gets_config_values()
    {
        $cacher = $this->cacher([
            'foo' => 'bar',
        ]);

        $this->assertEquals('bar', $cacher->config('foo'));
        $this->assertEquals('qux', $cacher->config('baz', 'qux'));
    }

    /** @test */
    public function gets_default_expiration()
    {
        $cacher = $this->cacher([
            'expiry' => 10,
        ]);

        $this->assertEquals(10, $cacher->getDefaultExpiration());
    }

    /** @test */
    public function gets_default_expiration_using_deprecated_key()
    {
        $cacher = $this->cacher([
            'default_cache_length' => 10,
        ]);

        $this->assertEquals(10, $cacher->getDefaultExpiration());
    }

    /** @test */
    public function gets_default_expiration_where_new_key_takes_precedence_over_deprecated_key()
    {
        $cacher = $this->cacher([
            'expiry' => 2,
            'default_cache_length' => 10,
        ]);

        $this->assertEquals(2, $cacher->getDefaultExpiration());
    }

    /** @test */
    public function gets_a_url()
    {
        $cacher = $this->cacher();

        $request = Request::create('http://example.com/test', 'GET', [
            'foo' => 'bar',
        ]);

        $this->assertEquals('http://example.com/test?foo=bar', $cacher->getUrl($request));
    }

    /** @test */
    public function gets_a_url_with_query_strings_disabled()
    {
        $cacher = $this->cacher(['ignore_query_strings' => true]);

        $request = Request::create('http://example.com/test', 'GET', [
            'foo' => 'bar',
        ]);

        $this->assertEquals('http://example.com/test', $cacher->getUrl($request));
    }

    /** @test */
    public function gets_the_base_url()
    {
        $cacher = $this->cacher(['base_url' => 'http://example.com']);

        $this->assertEquals('http://example.com', $cacher->getBaseUrl());
    }

    /** @test */
    public function gets_domains()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher();

        $cache->forever('static-cache:domains', ['http://example.com']);

        $this->assertEquals(['http://example.com'], $cacher->getDomains()->all());
    }

    /** @test */
    public function caches_a_url()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher(['base_url' => 'http://example.com']);

        $this->assertNull($cache->get('static-cache:urls'));
        $this->assertNull($cache->get('static-cache:domains'));

        $cacher->cacheUrl('one', 'http://example.com/one');

        $domains = $cache->get('static-cache:domains');
        $urls = $cache->get('static-cache:'.md5('http://example.com').'.urls');

        $this->assertEquals(['http://example.com'], $domains);
        $this->assertEquals(['one' => '/one'], $urls);
    }

    /** @test */
    public function gets_urls()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher(['base_url' => 'http://example.com']);

        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one',
        ]);

        $cacher->cacheUrl('two', '/two');

        $urls = $cacher->getUrls();

        $this->assertInstanceOf(Collection::class, $urls);
        $this->assertEquals(['one' => '/one', 'two' => '/two'], $urls->all());
    }

    /** @test */
    public function forgets_a_url()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher(['base_url' => 'http://example.com']);

        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one', 'two' => '/two',
        ]);

        $cacher->forgetUrl('one');

        $this->assertEquals(['two' => '/two'], $cacher->getUrls()->all());
    }

    /** @test */
    public function flushes_urls()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher(['base_url' => 'http://example.com']);

        $cache->forever('static-cache:domains', [
            'http://example.com',
            'http://example.co.uk',
        ]);

        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one', 'two' => '/two',
        ]);
        $cache->forever('static-cache:'.md5('http://example.co.uk').'.urls', [
            'three' => '/three', 'four' => '/four',
        ]);

        $this->assertEquals(2, $cacher->getDomains()->count());
        $this->assertEquals(2, $cacher->getUrls()->count());

        $cacher->flushUrls();

        $this->assertEquals(0, $cacher->getUrls('http://example.com')->count());
        $this->assertEquals(0, $cacher->getUrls('http://example.co.uk')->count());
        $this->assertEquals(0, $cacher->getDomains()->count());
    }

    /** @test */
    public function excludes_urls()
    {
        $cacher = $this->cacher(['exclude' => ['/blog']]);

        $this->assertTrue($cacher->isExcluded('/blog'));
        $this->assertFalse($cacher->isExcluded('/blog/post'));
    }

    /** @test */
    public function excludes_wildcard_urls()
    {
        $cacher = $this->cacher(['exclude' => [
            '/blog/*', // The slash indicates "only child pages"
            '/news*',   // No slash would get the "news" page, child pages, and any page with the substring.
        ]]);

        $this->assertTrue($cacher->isExcluded('/blog/post'));
        $this->assertFalse($cacher->isExcluded('/blog'));

        $this->assertTrue($cacher->isExcluded('/news'));
        $this->assertTrue($cacher->isExcluded('/news/article'));
        $this->assertTrue($cacher->isExcluded('/newspaper'));
    }

    /** @test */
    public function url_exclusions_ignore_query_strings()
    {
        $cacher = $this->cacher(['exclude' => ['/blog']]);

        $this->assertTrue($cacher->isExcluded('/blog?page=1'));
    }

    /** @test */
    public function url_exclusions_trim_the_base_url()
    {
        $cacher = $this->cacher([
            'base_url' => 'http://example.com',
            'exclude' => ['/blog'],
        ]);

        $this->assertTrue($cacher->isExcluded('http://example.com/blog'));
    }

    private function cacher($config = [])
    {
        return new TestCacher(app(Repository::class), $config);
    }
}

class TestCacher extends AbstractCacher
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

    public function invalidateUrl($url)
    {
    }
}
