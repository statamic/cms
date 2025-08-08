<?php

namespace Tests\StaticCaching;

use Illuminate\Cache\Repository;
use Illuminate\Support\Collection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\Cachers\AbstractCacher;
use Tests\TestCase;

class CacherTest extends TestCase
{
    #[Test]
    public function gets_config_values()
    {
        $cacher = $this->cacher([
            'foo' => 'bar',
        ]);

        $this->assertEquals('bar', $cacher->config('foo'));
        $this->assertEquals('qux', $cacher->config('baz', 'qux'));
    }

    #[Test]
    public function gets_default_expiration()
    {
        $cacher = $this->cacher([
            'expiry' => 10,
        ]);

        $this->assertEquals(10, $cacher->getDefaultExpiration());
    }

    #[Test]
    public function gets_the_base_url_using_the_deprecated_config_value()
    {
        $cacher = $this->cacher(['base_url' => 'http://example.com']);

        $this->assertEquals('http://example.com', $cacher->getBaseUrl());
    }

    #[Test]
    public function gets_the_base_url_using_the_deprecated_config_value_with_trailing_slash()
    {
        $cacher = $this->cacher(['base_url' => 'http://example.com/']);

        $this->assertEquals('http://example.com', $cacher->getBaseUrl());
    }

    #[Test]
    public function gets_the_base_url_when_sites_have_absolute_urls()
    {
        $this->setSites([
            'default' => ['url' => 'http://example.com'],
            'uk' => ['url' => 'http://example.co.uk'],
        ]);

        $cacher = $this->cacher();

        $this->assertEquals('http://example.com', $cacher->getBaseUrl());
    }

    #[Test]
    public function gets_the_base_url_when_sites_have_absolute_urls_with_trailing_slashes()
    {
        $this->setSites([
            'default' => ['url' => 'http://example.com/'],
            'uk' => ['url' => 'http://example.co.uk/'],
        ]);

        $cacher = $this->cacher();

        $this->assertEquals('http://example.com', $cacher->getBaseUrl());
    }

    #[Test]
    public function gets_the_base_url_when_sites_have_relative_urls()
    {
        $this->setSites([
            'default' => ['url' => '/default'],
            'uk' => ['url' => '/uk'],
        ]);

        config(['app.url' => 'http://app.com']);

        $cacher = $this->cacher();

        $this->assertEquals('http://app.com/default', $cacher->getBaseUrl());
    }

    #[Test]
    public function gets_the_base_url_when_sites_have_relative_urls_with_trailing_slashes()
    {
        $this->setSites([
            'default' => ['url' => '/default/'],
            'uk' => ['url' => '/uk/'],
        ]);

        config(['app.url' => 'http://app.com']);

        $cacher = $this->cacher();

        $this->assertEquals('http://app.com/default', $cacher->getBaseUrl());
    }

    #[Test]
    public function gets_the_base_url_when_site_is_just_a_slash()
    {
        $this->setSites([
            'default' => ['url' => '/'],
            'uk' => ['url' => '/uk/'],
        ]);

        config(['app.url' => 'http://app.com']);

        $cacher = $this->cacher();

        $this->assertEquals('http://app.com', $cacher->getBaseUrl());
    }

    #[Test]
    public function gets_domains()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher();

        $cache->forever('static-cache:domains', ['http://example.com']);

        $this->assertEquals(['http://example.com'], $cacher->getDomains()->all());
    }

    #[Test]
    public function caches_a_url()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher(['base_url' => 'http://example.com']);

        $this->assertNull($cache->get('static-cache:urls'));
        $this->assertNull($cache->get('static-cache:domains'));

        $cacher->cacheUrl('one', 'http://example.com/one');
        $cacher->cacheUrl('two', '/two');
        $cacher->cacheUrl('three', 'http://example.org/three', 'http://example.org');

        $domains = $cache->get('static-cache:domains');
        $urls = $cache->get('static-cache:'.md5('http://example.com').'.urls');

        $this->assertEquals(['http://example.com', 'http://example.org'], $domains);
        $this->assertEquals(['one' => '/one', 'two' => '/two'], $urls);
    }

    #[Test]
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

    #[Test]
    public function forgets_a_url()
    {
        $cache = app(Repository::class);
        $cacher = $this->cacher(['base_url' => 'http://example.com']);

        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one', 'two' => '/two',
        ]);
        $cache->forever('static-cache:'.md5('http://example.org').'.urls', [
            'one' => '/one', 'two' => '/two',
        ]);

        $cacher->forgetUrl('one');
        $cacher->forgetUrl('two', 'http://example.org');

        $this->assertEquals(['two' => '/two'], $cacher->getUrls()->all());
        $this->assertEquals(['two' => '/two'], $cacher->getUrls('http://example.com')->all());
        $this->assertEquals(['one' => '/one'], $cacher->getUrls('http://example.org')->all());
    }

    #[Test]
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

    #[Test]
    public function it_asks_the_url_excluder_if_a_url_should_be_excluder()
    {
        $mock = Mockery::mock(UrlExcluder::class);
        $mock->shouldReceive('isExcluded')->with('/foo')->andReturnTrue()->once();
        $mock->shouldReceive('isExcluded')->with('/bar')->andReturnFalse()->once();
        app()->instance('mock-url-excluder', $mock);

        config(['statamic.static_caching.exclude' => [
            'class' => 'mock-url-excluder',
        ]]);

        $cacher = $this->cacher();

        $this->assertTrue($cacher->isExcluded('/foo'));
        $this->assertFalse($cacher->isExcluded('/bar'));
    }

    #[Test]
    public function it_invalidates_urls()
    {
        $cache = app(Repository::class);

        $this->setSites([
            'default' => ['url' => 'http://example.com'],
            'uk' => ['url' => 'http://example.co.uk'],
        ]);

        $cache->forever('static-cache:domains', [
            'http://example.com',
            'http://example.co.uk',
        ]);

        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'blog' => '/blog', 'blog-post' => '/blog/post', 'contact' => '/contact',
        ]);
        $cache->forever('static-cache:'.md5('http://example.co.uk').'.urls', [
            'blog' => '/blog', 'blog-post' => '/blog/post', 'contact' => '/contact',
        ]);

        $cacher = $this->cacher();

        $cacher->shouldReceive('invalidateUrl')->once()->with('/', 'http://example.com');
        $cacher->shouldReceive('invalidateUrl')->twice()->with('/one', 'http://example.com');
        $cacher->shouldReceive('invalidateUrl')->once()->with('/two', 'http://example.com');
        $cacher->shouldReceive('invalidateUrl')->once()->with('/three', 'http://example.co.uk');
        $cacher->shouldReceive('invalidateUrl')->times(3)->with('/blog/post', 'http://example.com');
        $cacher->shouldReceive('invalidateUrl')->once()->with('/blog/post', 'http://example.co.uk');

        $cacher->invalidateUrls([
            '/',
            '/one',
            'one',
            'http://example.com/two',
            'http://example.co.uk/three',
            '/blog/*',
            'blog/*',
            'http://example.com/blog/*',
            'http://example.co.uk/blog/*',
        ]);
    }

    private function cacher($config = [])
    {
        return Mockery::mock(AbstractCacher::class, [app(Repository::class), $config])->makePartial();
    }
}
