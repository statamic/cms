<?php

namespace Tests\Facades;

use Statamic\Facades\Site;
use Statamic\Facades\URL;
use Tests\TestCase;

class UrlTest extends TestCase
{
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.url', 'http://absolute-url-resolved-from-request.com');
    }

    public function testPrependsSiteUrl()
    {
        Site::setConfig('sites.en.url', 'http://site.com/');

        $this->assertEquals(
            'http://site.com/foo',
            URL::prependSiteUrl('/foo')
        );
    }

    public function testPrependsSiteUrlWithController()
    {
        Site::setConfig('sites.en.url', 'http://site.com/index.php/');

        $this->assertEquals(
            'http://site.com/index.php/foo',
            URL::prependSiteUrl('/foo')
        );
    }

    public function testPrependsSiteUrlWithoutController()
    {
        // Override with what would be used on a normal request.
        request()->server->set('SCRIPT_NAME', '/index.php');

        Site::setConfig('sites.en.url', 'http://site.com/index.php/');

        $this->assertEquals(
            'http://site.com/foo',
            URL::prependSiteUrl('/foo', null, false)
        );
    }

    public function testDeterminesExternalUrl()
    {
        Site::setConfig('sites.en.url', 'http://this-site.com/');
        $this->assertTrue(URL::isExternal('http://that-site.com'));
        $this->assertTrue(URL::isExternal('http://that-site.com/'));
        $this->assertTrue(URL::isExternal('http://that-site.com/some-slug'));
        $this->assertFalse(URL::isExternal('http://this-site.com'));
        $this->assertFalse(URL::isExternal('http://this-site.com/'));
        $this->assertFalse(URL::isExternal('http://this-site.com/some-slug'));
        $this->assertFalse(URL::isExternal('/foo'));
        $this->assertFalse(URL::isExternal('#anchor'));
        $this->assertFalse(URL::isExternal(''));
        $this->assertFalse(URL::isExternal(null));
    }

    public function testDeterminesExternalUrlWhenUsingRelativeInConfig()
    {
        Site::setConfig('sites.en.url', '/');
        $this->assertTrue(URL::isExternal('http://that-site.com'));
        $this->assertTrue(URL::isExternal('http://that-site.com/'));
        $this->assertTrue(URL::isExternal('http://that-site.com/some-slug'));
        $this->assertFalse(URL::isExternal('http://absolute-url-resolved-from-request.com'));
        $this->assertFalse(URL::isExternal('http://absolute-url-resolved-from-request.com/'));
        $this->assertFalse(URL::isExternal('http://absolute-url-resolved-from-request.com/some-slug'));
        $this->assertFalse(URL::isExternal('/foo'));
        $this->assertFalse(URL::isExternal('#anchor'));
        $this->assertFalse(URL::isExternal(''));
        $this->assertFalse(URL::isExternal(null));
    }

    /**
     * @test
     *
     * @dataProvider ancestorProvider
     **/
    public function it_checks_whether_a_url_is_an_ancestor_of_another($child, $parent, $isAncestor)
    {
        $this->assertSame($isAncestor, URL::isAncestorOf($child, $parent));
    }

    public function ancestorProvider()
    {
        return [
            'homepage to homepage' => ['/', '/', false],
            'directory to homepage' => ['/foo', '/', true],
            'nested directory to homepage' => ['/foo/bar', '/', true],
            'nested directory to directory' => ['/foo/bar', '/foo', true],
            'directory to nested directory' => ['/foo', '/foo/bar', false],
            'homepage to nested directory' => ['/', '/foo', false],

            'directory to directory with similar name' => ['/about-me', '/about', false],
            'directory with trailing slash to directory with similar name' => ['/about-me/', '/about', false],
            'directory to directory with similar name with trailing slash ' => ['/about-me/', '/about/', false],

            'nested directory to directory with trailing slashes' => ['/foo/bar', '/foo/', true],
            'directory to nested directory with trailing slashes' => ['/foo', '/foo/bar/', false],
            'homepage to nested directory with trailing slashes' => ['/', '/foo/', false],

            'nested directory with trailing slashes to directory' => ['/foo/bar/', '/foo', true],
            'directory with trailing slashes to nested directory' => ['/foo/', '/foo/bar', false],

            'homepage with query string to homepage' => ['/?baz=qux', '/', false],
            'directory with query string  to homepage' => ['/foo?baz=qux', '/', true],
            'nested directory with query string  to homepage' => ['/foo/bar?baz=qux', '/', true],
            'nested directory with query string  to directory' => ['/foo/bar?baz=qux', '/foo', true],
            'directory with query string  to nested directory' => ['/foo?baz=qux', '/foo/bar', false],
            'homepage with query string  to nested directory' => ['/?baz=qux', '/foo', false],
        ];
    }

    /**
     * @test
     *
     * @dataProvider relativeProvider
     **/
    public function makes_urls_relative($url, $expected)
    {
        $this->assertSame($expected, URL::makeRelative($url));
    }

    public function relativeProvider()
    {
        return [
            ['http://example.com', '/'],
            ['http://example.com/', '/'],
            ['http://example.com/foo', '/foo'],
            ['http://example.com/foo/', '/foo/'],
            ['http://example.com/foo/bar', '/foo/bar'],
            ['http://example.com/foo/bar/', '/foo/bar/'],
            ['/', '/'],
            ['/foo', '/foo'],
            ['/foo/', '/foo/'],
            ['/foo/bar', '/foo/bar'],
            ['/foo/bar/', '/foo/bar/'],

            ['http://example.com?bar=baz', '/?bar=baz'],
            ['http://example.com/?bar=baz', '/?bar=baz'],
            ['http://example.com/foo?bar=baz', '/foo?bar=baz'],
            ['http://example.com/foo/?bar=baz', '/foo/?bar=baz'],
            ['http://example.com/foo/bar?bar=baz', '/foo/bar?bar=baz'],
            ['http://example.com/foo/bar/?bar=baz', '/foo/bar/?bar=baz'],
            ['/?bar=baz', '/?bar=baz'],
            ['/foo?bar=baz', '/foo?bar=baz'],
            ['/foo/?bar=baz', '/foo/?bar=baz'],
            ['/foo/bar?bar=baz', '/foo/bar?bar=baz'],
            ['/foo/bar/?bar=baz', '/foo/bar/?bar=baz'],

            ['http://example.com#fragment', '/#fragment'],
            ['http://example.com/#fragment', '/#fragment'],
            ['http://example.com/foo#fragment', '/foo#fragment'],
            ['http://example.com/foo/#fragment', '/foo/#fragment'],
            ['http://example.com/foo/bar#fragment', '/foo/bar#fragment'],
            ['http://example.com/foo/bar/#fragment', '/foo/bar/#fragment'],
            ['/#fragment', '/#fragment'],
            ['/foo#fragment', '/foo#fragment'],
            ['/foo/#fragment', '/foo/#fragment'],
            ['/foo/bar#fragment', '/foo/bar#fragment'],
            ['/foo/bar/#fragment', '/foo/bar/#fragment'],

            ['http://example.com?bar=baz#fragment', '/?bar=baz#fragment'],
            ['http://example.com/?bar=baz#fragment', '/?bar=baz#fragment'],
            ['http://example.com/foo?bar=baz#fragment', '/foo?bar=baz#fragment'],
            ['http://example.com/foo/?bar=baz#fragment', '/foo/?bar=baz#fragment'],
            ['http://example.com/foo/bar?bar=baz#fragment', '/foo/bar?bar=baz#fragment'],
            ['http://example.com/foo/bar/?bar=baz#fragment', '/foo/bar/?bar=baz#fragment'],
            ['/?bar=baz#fragment', '/?bar=baz#fragment'],
            ['/foo?bar=baz#fragment', '/foo?bar=baz#fragment'],
            ['/foo/?bar=baz#fragment', '/foo/?bar=baz#fragment'],
            ['/foo/bar?bar=baz#fragment', '/foo/bar?bar=baz#fragment'],
            ['/foo/bar/?bar=baz#fragment', '/foo/bar/?bar=baz#fragment'],
        ];
    }
}
