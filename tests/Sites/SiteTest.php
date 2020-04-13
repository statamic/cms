<?php

namespace Tests\Sites;

use Tests\TestCase;
use Statamic\Sites\Site;
use Statamic\Sites\Sites;

class SiteTest extends TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.url', 'http://absolute-url-resolved-from-request.com');
    }

    /** @test */
    function gets_handle()
    {
        $site = new Site('en', []);

        $this->assertEquals('en', $site->handle());
    }

    /** @test */
    function gets_name()
    {
        $site = new Site('en', ['name' => 'English']);

        $this->assertEquals('English', $site->name());
    }

    /** @test */
    function gets_locale()
    {
        $site = new Site('en', ['locale' => 'en_US']);

        $this->assertEquals('en_US', $site->locale());
    }

    /** @test */
    function gets_short_locale()
    {
        $this->assertEquals('en', (new Site('en', ['locale' => 'en']))->shortLocale());
        $this->assertEquals('en', (new Site('en', ['locale' => 'en_US']))->shortLocale());
        $this->assertEquals('en', (new Site('en', ['locale' => 'en-US']))->shortLocale());
    }

    /** @test */
    function gets_url()
    {
        $site = new Site('en', ['url' => 'http://test.com/']);

        $this->assertEquals('http://test.com/', $site->url());
    }

    /** @test */
    function gets_url_without_trailing_slash()
    {
        $site = new Site('en', ['url' => 'http://test.com']);

        $this->assertEquals('http://test.com/', $site->url());
    }

    /** @test */
    function gets_absolute_url()
    {
        $this->assertEquals(
            'http://a-defined-absolute-url.com/',
            (new Site('en', ['url' => 'http://a-defined-absolute-url.com/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://a-defined-absolute-url.com/',
            (new Site('en', ['url' => 'http://a-defined-absolute-url.com']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/',
            (new Site('en', ['url' => '/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/fr/',
            (new Site('en', ['url' => '/fr/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/fr/',
            (new Site('en', ['url' => '/fr']))->absoluteUrl()
        );

        $this->get('/something');

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/',
            (new Site('en', ['url' => '/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/fr/',
            (new Site('en', ['url' => '/fr/']))->absoluteUrl()
        );

        $this->assertEquals(
            'http://absolute-url-resolved-from-request.com/fr/',
            (new Site('en', ['url' => '/fr']))->absoluteUrl()
        );
    }

    /** @test */
    function gets_path()
    {
        tap(new Site('en', ['url' => 'http://test.com/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com'));
            $this->assertEquals('/', $site->relativePath('http://test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar/'));
        });

        tap(new Site('en', ['url' => 'http://test.com']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com'));
            $this->assertEquals('/', $site->relativePath('http://test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar/'));
        });

        tap(new Site('fr', ['url' => 'http://test.com/fr/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com/fr'));
            $this->assertEquals('/', $site->relativePath('http://test.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar/'));
        });

        tap(new Site('fr', ['url' => 'http://test.com/fr']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com/fr'));
            $this->assertEquals('/', $site->relativePath('http://test.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar/'));
        });

        tap(new Site('sub', ['url' => 'http://subdomain.test.com/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com'));
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar/'));
        });

        tap(new Site('sub', ['url' => 'http://subdomain.test.com']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com'));
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar/'));
        });

        tap(new Site('en', ['url' => '/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com'));
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/foo/bar/'));
        });

        tap(new Site('fr', ['url' => '/fr/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/fr'));
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/bar/'));
        });

        tap(new Site('fr', ['url' => '/fr']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/fr'));
            $this->assertEquals('/', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo'));
            $this->assertEquals('/foo', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/bar'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://absolute-url-resolved-from-request.com/fr/foo/bar/'));
        });
    }
}
