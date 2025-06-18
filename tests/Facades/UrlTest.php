<?php

namespace Tests\Facades;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\URL;
use Statamic\Support\Str;
use Tests\TestCase;

class UrlTest extends TestCase
{
    public function tearDown(): void
    {
        URL::enforceTrailingSlashes(false);

        parent::tearDown();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.url', 'http://absolute-url-resolved-from-request.com');
    }

    #[Test]
    public function it_prepends_site_url()
    {
        $this->setSiteValue('en', 'url', 'http://site.com/');

        $this->assertEquals('http://site.com/foo', URL::prependSiteUrl('/foo'));

        URL::enforceTrailingSlashes();

        $this->assertEquals('http://site.com/foo/', URL::prependSiteUrl('/foo'));
    }

    #[Test]
    public function it_prepends_site_url_with_controller()
    {
        $this->setSiteValue('en', 'url', 'http://site.com/index.php/');

        $this->assertEquals('http://site.com/index.php/foo', URL::prependSiteUrl('/foo'));

        URL::enforceTrailingSlashes();

        $this->assertEquals('http://site.com/index.php/foo/', URL::prependSiteUrl('/foo'));
    }

    #[Test]
    public function it_prepends_site_url_without_controller()
    {
        // Override with what would be used on a normal request.
        request()->server->set('SCRIPT_NAME', '/index.php');

        $this->setSiteValue('en', 'url', 'http://site.com/index.php/');

        $this->assertEquals('http://site.com/foo', URL::prependSiteUrl('/foo', null, false));

        URL::enforceTrailingSlashes();

        $this->assertEquals('http://site.com/foo/', URL::prependSiteUrl('/foo', null, false));
    }

    #[Test]
    public function it_removes_site_url()
    {
        $this->setSiteValue('en', 'url', 'http://site.com/');

        $this->assertEquals('/', URL::removeSiteUrl('http://site.com'));
        $this->assertEquals('/foo', URL::removeSiteUrl('http://site.com/foo'));
        $this->assertEquals('/foo', URL::removeSiteUrl('http://site.com/foo/'));
        $this->assertEquals('http://not-site.com/foo', URL::removeSiteUrl('http://not-site.com/foo/'));

        URL::enforceTrailingSlashes();

        $this->assertEquals('/', URL::removeSiteUrl('http://site.com/'));
        $this->assertEquals('/foo/', URL::removeSiteUrl('http://site.com/foo'));
        $this->assertEquals('/foo/', URL::removeSiteUrl('http://site.com/foo/'));
        $this->assertEquals('http://not-site.com/foo/', URL::removeSiteUrl('http://not-site.com/foo/'));
    }

    #[Test]
    public function it_determines_external_url()
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        $this->assertTrue(URL::isExternal('http://that-site.com'));
        $this->assertTrue(URL::isExternal('http://that-site.com/'));
        $this->assertTrue(URL::isExternal('http://that-site.com/some-slug'));
        $this->assertTrue(URL::isExternal('http://that-site.com/some-slug?foo'));
        $this->assertTrue(URL::isExternal('http://that-site.com/some-slug#anchor'));
        $this->assertFalse(URL::isExternal('http://this-site.com'));
        $this->assertFalse(URL::isExternal('http://this-site.com/'));
        $this->assertFalse(URL::isExternal('http://this-site.com/some-slug'));
        $this->assertFalse(URL::isExternal('/foo'));
        $this->assertFalse(URL::isExternal('#anchor'));
        $this->assertFalse(URL::isExternal(''));
        $this->assertFalse(URL::isExternal(null));
    }

    #[Test]
    public function it_determines_external_url_when_using_relative_in_config()
    {
        $this->setSiteValue('en', 'url', '/');
        $this->assertTrue(URL::isExternal('http://that-site.com'));
        $this->assertTrue(URL::isExternal('http://that-site.com/'));
        $this->assertTrue(URL::isExternal('http://that-site.com/some-slug'));
        $this->assertTrue(URL::isExternal('http://that-site.com/some-slug?foo'));
        $this->assertTrue(URL::isExternal('http://that-site.com/some-slug#anchor'));
        $this->assertFalse(URL::isExternal('http://absolute-url-resolved-from-request.com'));
        $this->assertFalse(URL::isExternal('http://absolute-url-resolved-from-request.com/'));
        $this->assertFalse(URL::isExternal('http://absolute-url-resolved-from-request.com/some-slug'));
        $this->assertFalse(URL::isExternal('/foo'));
        $this->assertFalse(URL::isExternal('#anchor'));
        $this->assertFalse(URL::isExternal(''));
        $this->assertFalse(URL::isExternal(null));
    }

    #[Test]
    public function it_determines_if_external_url_to_application()
    {
        $this->setSites([
            'first' => ['name' => 'First', 'locale' => 'en_US', 'url' => 'http://this-site.com/'],
            'third' => ['name' => 'Third', 'locale' => 'en_US', 'url' => 'http://subdomain.this-site.com/'],
            'second' => ['name' => 'Second', 'locale' => 'fr_FR', 'url' => '/fr/'],
        ]);

        $this->assertTrue(URL::isExternalToApplication('http://that-site.com'));
        $this->assertTrue(URL::isExternalToApplication('http://that-site.com/'));
        $this->assertTrue(URL::isExternalToApplication('http://that-site.com/some-slug'));
        $this->assertTrue(URL::isExternalToApplication('http://that-site.com/some-slug?foo'));
        $this->assertTrue(URL::isExternalToApplication('http://that-site.com/some-slug#anchor'));

        $this->assertFalse(URL::isExternalToApplication('http://subdomain.this-site.com'));
        $this->assertFalse(URL::isExternalToApplication('http://subdomain.this-site.com/'));
        $this->assertFalse(URL::isExternalToApplication('http://subdomain.this-site.com/some-slug'));
        $this->assertFalse(URL::isExternalToApplication('http://subdomain.this-site.com/some-slug?foo'));
        $this->assertFalse(URL::isExternalToApplication('http://subdomain.this-site.com/some-slug#anchor'));

        // TODO...
        // $this->assertFalse(URL::isExternalToApplication('http://absolute-url-resolved-from-request.com'));
        // $this->assertFalse(URL::isExternalToApplication('http://absolute-url-resolved-from-request.com/'));
        // $this->assertFalse(URL::isExternalToApplication('http://absolute-url-resolved-from-request.com/some-slug'));
        // $this->assertFalse(URL::isExternalToApplication('/foo'));
        // $this->assertFalse(URL::isExternalToApplication('#anchor'));
        // $this->assertFalse(URL::isExternalToApplication(''));
        // $this->assertFalse(URL::isExternalToApplication(null));
    }

    #[Test]
    #[DataProvider('assembleProvider')]
    public function it_can_assemble_urls($segments, $assembled)
    {
        $this->assertSame($assembled, URL::assemble(...$segments));

        URL::enforceTrailingSlashes();

        $parts = str($assembled)
            ->split(pattern: '/([?#])/', flags: PREG_SPLIT_DELIM_CAPTURE)
            ->all();

        $url = array_shift($parts);
        $queryAndFragments = implode($parts);
        $assembledWithTrailingSlash = Str::ensureRight($url, '/').$queryAndFragments;

        $this->assertSame($assembledWithTrailingSlash, URL::assemble(...$segments));
    }

    public static function assembleProvider()
    {
        return [
            'relative homepage' => [['/'], '/'],
            'absolute homepage' => [['http://localhost'], 'http://localhost'],
            'absolute homepage with trailing slash' => [['http://localhost/'], 'http://localhost'],

            'relative route' => [['/', 'foo'], '/foo'],
            'relative route with trailing slash' => [['/', 'foo/'], '/foo'],
            'absolute route' => [['http://localhost', 'foo'], 'http://localhost/foo'],
            'absolute route with trailing slashes' => [['http://localhost/', 'foo/'], 'http://localhost/foo'],

            'relative nested route' => [['/', 'foo', 'bar'], '/foo/bar'],
            'relative nested route with trailing slashes' => [['/', 'foo/', 'bar/'], '/foo/bar'],
            'absolute nested route' => [['http://localhost', 'foo', 'bar'], 'http://localhost/foo/bar'],
            'absolute nested route with trailing slashes' => [['http://localhost/', 'foo/', 'bar/'], 'http://localhost/foo/bar'],

            'with query from relative url' => [['/', 'entries', 'foo', '?alpha'], '/entries/foo?alpha'],
            'with query from relative url with trailing slashes' => [['/', 'entries/', 'foo/', '?alpha'], '/entries/foo?alpha'],
            'with query from absolute url' => [['http://localhost', 'entries', 'foo', '?alpha'], 'http://localhost/entries/foo?alpha'],
            'with query from absolute url with trailing slashes' => [['http://localhost/', 'entries/', 'foo/', '?alpha'], 'http://localhost/entries/foo?alpha'],
            'with anchor fragment from relative url' => [['/', 'entries', 'foo', '#alpha'], '/entries/foo#alpha'],
            'with anchor fragment from relative url with trailing slashes' => [['/', 'entries/', 'foo/', '#alpha'], '/entries/foo#alpha'],
            'with anchor fragment from absolute url' => [['http://localhost', 'entries', 'foo', '#alpha'], 'http://localhost/entries/foo#alpha'],
            'with anchor fragment from absolute url with trailing slashes' => [['http://localhost/', 'entries/', 'foo/', '#alpha'], 'http://localhost/entries/foo#alpha'],
            'with query and anchor fragment from relative url' => [['/', 'entries', 'foo', '?alpha#beta'], '/entries/foo?alpha#beta'],
            'with query and anchor fragment from relative url with trailing slashes' => [['/', 'entries/', 'foo/', '?alpha#beta'], '/entries/foo?alpha#beta'],
            'with query and anchor fragment from absolute url' => [['http://localhost', 'entries', 'foo', '?alpha#beta'], 'http://localhost/entries/foo?alpha#beta'],
            'with query and anchor fragment from absolute url with trailing slashes' => [['http://localhost/', 'entries/', 'foo/', '?alpha#beta'], 'http://localhost/entries/foo?alpha#beta'],
        ];
    }

    #[Test]
    #[DataProvider('slugProvider')]
    public function it_gets_the_slug_at_the_end_of_a_url($url, $slug)
    {
        $this->assertSame($slug, URL::slug($url));
    }

    public static function slugProvider()
    {
        return [
            'relative homepage should have no slug' => ['/', null],
            'absolute homepage should have no slug' => ['http://localhost', null],
            'absolute homepage with trailing slash should have no slug' => ['http://localhost/', null],

            'relative route to slug' => ['/foo', 'foo'],
            'relative route to slug with trailing slash' => ['/foo/', 'foo'],
            'absolute route to slug' => ['http://localhost/foo', 'foo'],
            'absolute route to slug with trailing slash' => ['http://localhost/foo/', 'foo'],

            'relative nested route to slug' => ['/entries/foo', 'foo'],
            'relative nested route to slug with trailing slash' => ['/entries/foo/', 'foo'],
            'absolute nested route to slug' => ['http://localhost/entries/foo', 'foo'],
            'absolute nested route to slug with trailing slash' => ['http://localhost/entries/foo/', 'foo'],

            'removes query from relative url' => ['/entries/foo?alpha', 'foo'],
            'removes query from relative url with trailing slash' => ['/entries/foo/?alpha', 'foo'],
            'removes query from absolute url' => ['http://localhost/entries/foo?alpha', 'foo'],
            'removes query from absolute url with trailing slash' => ['http://localhost/entries/foo/?alpha', 'foo'],
            'removes anchor fragment from relative url' => ['/entries/foo#alpha', 'foo'],
            'removes anchor fragment from relative url with trailing slash' => ['/entries/foo/#alpha', 'foo'],
            'removes anchor fragment from absolute url' => ['http://localhost/entries/foo#alpha', 'foo'],
            'removes anchor fragment from absolute url with trailing slash' => ['http://localhost/entries/foo/#alpha', 'foo'],
            'removes query and anchor fragment from relative url' => ['/entries/foo?alpha#beta', 'foo'],
            'removes query and anchor fragment from relative url with trailing slash' => ['/entries/foo/?alpha#beta', 'foo'],
            'removes query and anchor fragment from absolute url' => ['http://localhost/entries/foo?alpha#beta', 'foo'],
            'removes query and anchor fragment from absolute url with trailing slash' => ['http://localhost/entries/foo/?alpha#beta', 'foo'],
        ];
    }

    #[Test]
    #[DataProvider('replaceSlugProvider')]
    public function it_replaces_the_slug_at_the_end_of_a_url($url, $replaced)
    {
        $this->assertSame($replaced, URL::replaceSlug($url, 'bar'));

        URL::enforceTrailingSlashes();

        $replaced = preg_replace('/localhost$/', 'localhost/', $replaced);
        $replaced = str_replace('bar', 'bar/', $replaced);

        $this->assertSame($replaced, URL::replaceSlug($url, 'bar'));
    }

    public static function replaceSlugProvider()
    {
        return [
            'relative homepage should have no slug' => ['/', '/'],
            'absolute homepage should have no slug' => ['http://localhost', 'http://localhost'],
            'absolute homepage with trailing slash should have no slug' => ['http://localhost/', 'http://localhost'],

            'relative route to slug' => ['/foo', '/bar'],
            'relative route to slug with trailing slash' => ['/foo/', '/bar'],
            'absolute route to slug' => ['http://localhost/foo', 'http://localhost/bar'],
            'absolute route to slug with trailing slash' => ['http://localhost/foo/', 'http://localhost/bar'],

            'relative nested route to slug' => ['/entries/foo', '/entries/bar'],
            'relative nested route to slug with trailing slash' => ['/entries/foo/', '/entries/bar'],
            'absolute nested route to slug' => ['http://localhost/entries/foo', 'http://localhost/entries/bar'],
            'absolute nested route to slug with trailing slash' => ['http://localhost/entries/bar', 'http://localhost/entries/bar'],

            'removes query from relative url' => ['/entries/foo?alpha', '/entries/bar?alpha'],
            'removes query from relative url with trailing slash' => ['/entries/foo/?alpha', '/entries/bar?alpha'],
            'removes query from absolute url' => ['http://localhost/entries/foo?alpha', 'http://localhost/entries/bar?alpha'],
            'removes query from absolute url with trailing slash' => ['http://localhost/entries/foo/?alpha', 'http://localhost/entries/bar?alpha'],
            'removes anchor fragment from relative url' => ['/entries/foo#alpha', '/entries/bar#alpha'],
            'removes anchor fragment from relative url with trailing slash' => ['/entries/foo/#alpha', '/entries/bar#alpha'],
            'removes anchor fragment from absolute url' => ['http://localhost/entries/foo#alpha', 'http://localhost/entries/bar#alpha'],
            'removes anchor fragment from absolute url with trailing slash' => ['http://localhost/entries/foo/#alpha', 'http://localhost/entries/bar#alpha'],
            'removes query and anchor fragment from relative url' => ['/entries/foo?alpha#beta', '/entries/bar?alpha#beta'],
            'removes query and anchor fragment from relative url with trailing slash' => ['/entries/foo/?alpha#beta', '/entries/bar?alpha#beta'],
            'removes query and anchor fragment from absolute url' => ['http://localhost/entries/foo?alpha#beta', 'http://localhost/entries/bar?alpha#beta'],
            'removes query and anchor fragment from absolute url with trailing slash' => ['http://localhost/entries/foo/?alpha#beta', 'http://localhost/entries/bar?alpha#beta'],
        ];
    }

    #[Test]
    #[DataProvider('parentProvider')]
    public function it_gets_the_parent_url($child, $parent)
    {
        $this->assertSame($parent, URL::parent($child));

        URL::enforceTrailingSlashes();

        $this->assertSame(Str::ensureRight($parent, '/'), URL::parent($child));
    }

    public static function parentProvider()
    {
        return [
            'relative homepage to homepage' => ['/', '/'],
            'absolute homepage to homepage' => ['http://localhost', 'http://localhost'],
            'absolute homepage to homepage with trailing slash' => ['http://localhost/', 'http://localhost'],

            'relative route to parent homepage' => ['/foo', '/'],
            'relative route to parent homepage with trailing slash' => ['/foo/', '/'],
            'absolute route to parent homepage' => ['http://localhost/foo', 'http://localhost'],
            'absolute route to parent homepage with trailing slash' => ['http://localhost/foo/', 'http://localhost'],

            'relative nested route to parent homepage' => ['/foo/bar', '/foo'],
            'relative nested route to parent homepage with trailing slash' => ['/foo/bar/', '/foo'],
            'absolute nested route to parent homepage' => ['http://localhost/foo/bar', 'http://localhost/foo'],
            'absolute nested route to parent homepage with trailing slash' => ['http://localhost/foo/bar/', 'http://localhost/foo'],

            'removes query from relative url' => ['/?alpha', '/'],
            'removes query from absolute url' => ['http://localhost/?alpha', 'http://localhost'],
            'removes anchor fragment from relative url' => ['/#alpha', '/'],
            'removes anchor fragment from absolute url' => ['http://localhost/#alpha', 'http://localhost'],
            'removes query and anchor fragment from relative url' => ['/?alpha#beta', '/'],
            'removes query and anchor fragment from absolute url' => ['http://localhost/?alpha#beta', 'http://localhost'],

            'preserves scheme and host' => ['https://example.com/foo/bar/', 'https://example.com/foo'],
        ];
    }

    #[Test]
    #[DataProvider('ancestorProvider')]
    public function it_checks_whether_a_url_is_an_ancestor_of_another($child, $parent, $isAncestor)
    {
        $this->assertSame($isAncestor, URL::isAncestorOf($child, $parent));
    }

    public static function ancestorProvider()
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
            'directory with query string to homepage' => ['/foo?baz=qux', '/', true],
            'nested directory with query string to homepage' => ['/foo/bar?baz=qux', '/', true],
            'nested directory with query string to directory' => ['/foo/bar?baz=qux', '/foo', true],
            'directory with query string to nested directory' => ['/foo?baz=qux', '/foo/bar', false],
            'homepage with query string to nested directory' => ['/?baz=qux', '/foo', false],

            'homepage with query string to homepage with query string' => ['/?baz=qux', '/?alpha=true', false],
            'directory with query string to homepage with query string' => ['/foo?baz=qux', '/?alpha=true', true],
            'nested directory with query string to homepage with query string' => ['/foo/bar?baz=qux', '/?alpha=true', true],
            'nested directory with query string to directory with query string' => ['/foo/bar?baz=qux', '/foo?alpha=true', true],
            'directory with query string to nested directory with query string' => ['/foo?baz=qux', '/foo/bar?alpha=true', false],
            'homepage with query string to nested directory with query string' => ['/?baz=qux', '/foo?alpha=true', false],

            'homepage with anchor fragment to homepage with anchor fragment' => ['/#alpha', '/#beta', false],
            'directory with anchor fragment to homepage with anchor fragment' => ['/foo#alpha', '/#beta', true],
            'nested directory with anchor fragment to homepage with anchor fragment' => ['/foo/bar#alpha', '/#beta', true],
            'nested directory with anchor fragment to directory with anchor fragment' => ['/foo/bar#alpha', '/foo#beta', true],
            'directory with anchor fragment to nested directory with anchor fragment' => ['/foo#alpha', '/foo/bar#beta', false],
            'homepage with anchor fragment to nested directory with anchor fragment' => ['/#alpha', '/foo#beta', false],
        ];
    }

    #[Test]
    public function gets_site_url()
    {
        $this->assertEquals('http://absolute-url-resolved-from-request.com', URL::getSiteUrl());

        \Illuminate\Support\Facades\URL::forceScheme('https');
        $this->assertEquals('https://absolute-url-resolved-from-request.com', URL::getSiteUrl());

        \Illuminate\Support\Facades\URL::forceScheme('http');
        $this->assertEquals('http://absolute-url-resolved-from-request.com', URL::getSiteUrl());
    }

    #[Test]
    #[DataProvider('absoluteProvider')]
    public function it_makes_urls_absolute($url, $expected, $forceScheme = false)
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        if ($forceScheme) {
            \Illuminate\Support\Facades\URL::forceScheme($forceScheme);
        }

        $this->assertSame($expected, URL::makeAbsolute($url));

        URL::enforceTrailingSlashes();

        $expected = Str::contains($url, 'external.com')
            ? $url
            : Str::ensureRight($expected, '/');

        $this->assertSame($expected, URL::makeAbsolute($url));
    }

    public static function absoluteProvider()
    {
        return [
            ['http://external.com', 'http://external.com'], // external absolute url provided, so url is left alone.
            ['http://external.com/', 'http://external.com/'], // external absolute url provided, so url is left alone.
            ['http://this-site.com/foo/', 'http://this-site.com/foo'], // already absolute, but we can still normalize trailing slashes
            ['/', 'http://absolute-url-resolved-from-request.com'],
            ['/foo', 'http://absolute-url-resolved-from-request.com/foo'],
            ['/foo/', 'http://absolute-url-resolved-from-request.com/foo'],

            ['http://external.com', 'http://external.com', 'https'], // external absolute url provided, so scheme and trailing slash are left alone.
            ['http://external.com/', 'http://external.com/', 'https'], // external absolute url provided, so scheme and trailing slash are left alone.
            ['/', 'https://absolute-url-resolved-from-request.com', 'https'],
            ['/foo', 'https://absolute-url-resolved-from-request.com/foo', 'https'],
            ['/foo/', 'https://absolute-url-resolved-from-request.com/foo', 'https'],

            ['https://external.com', 'https://external.com', 'http'], // external absolute url provided, so scheme and trailing slash are left alone.
            ['https://external.com/', 'https://external.com/', 'http'], // external absolute url provided, so scheme and trailing slash are left alone.
            ['/', 'http://absolute-url-resolved-from-request.com', 'http'],
            ['/foo', 'http://absolute-url-resolved-from-request.com/foo', 'http'],
            ['/foo/', 'http://absolute-url-resolved-from-request.com/foo', 'http'],
        ];
    }

    #[Test]
    #[DataProvider('relativeProvider')]
    public function makes_urls_relative($url, $expected)
    {
        $this->assertSame($expected, URL::makeRelative($url));
    }

    public static function relativeProvider()
    {
        return [
            ['http://example.com', '/'],
            ['http://example.com/', '/'],
            ['http://example.com/foo', '/foo'],
            ['http://example.com/foo/', '/foo'],
            ['http://example.com/foo/bar', '/foo/bar'],
            ['http://example.com/foo/bar/', '/foo/bar'],
            ['/', '/'],
            ['/foo', '/foo'],
            ['/foo/', '/foo'],
            ['/foo/bar', '/foo/bar'],
            ['/foo/bar/', '/foo/bar'],

            ['http://example.com?bar=baz', '/?bar=baz'],
            ['http://example.com/?bar=baz', '/?bar=baz'],
            ['http://example.com/foo?bar=baz', '/foo?bar=baz'],
            ['http://example.com/foo/?bar=baz', '/foo?bar=baz'],
            ['http://example.com/foo/bar?bar=baz', '/foo/bar?bar=baz'],
            ['http://example.com/foo/bar/?bar=baz', '/foo/bar?bar=baz'],
            ['/?bar=baz', '/?bar=baz'],
            ['/foo?bar=baz', '/foo?bar=baz'],
            ['/foo/?bar=baz', '/foo?bar=baz'],
            ['/foo/bar?bar=baz', '/foo/bar?bar=baz'],
            ['/foo/bar/?bar=baz', '/foo/bar?bar=baz'],

            ['http://example.com#fragment', '/#fragment'],
            ['http://example.com/#fragment', '/#fragment'],
            ['http://example.com/foo#fragment', '/foo#fragment'],
            ['http://example.com/foo/#fragment', '/foo#fragment'],
            ['http://example.com/foo/bar#fragment', '/foo/bar#fragment'],
            ['http://example.com/foo/bar/#fragment', '/foo/bar#fragment'],
            ['/#fragment', '/#fragment'],
            ['/foo#fragment', '/foo#fragment'],
            ['/foo/#fragment', '/foo#fragment'],
            ['/foo/bar#fragment', '/foo/bar#fragment'],
            ['/foo/bar/#fragment', '/foo/bar#fragment'],

            ['http://example.com?bar=baz#fragment', '/?bar=baz#fragment'],
            ['http://example.com/?bar=baz#fragment', '/?bar=baz#fragment'],
            ['http://example.com/foo?bar=baz#fragment', '/foo?bar=baz#fragment'],
            ['http://example.com/foo/?bar=baz#fragment', '/foo?bar=baz#fragment'],
            ['http://example.com/foo/bar?bar=baz#fragment', '/foo/bar?bar=baz#fragment'],
            ['http://example.com/foo/bar/?bar=baz#fragment', '/foo/bar?bar=baz#fragment'],
            ['/?bar=baz#fragment', '/?bar=baz#fragment'],
            ['/foo?bar=baz#fragment', '/foo?bar=baz#fragment'],
            ['/foo/?bar=baz#fragment', '/foo?bar=baz#fragment'],
            ['/foo/bar?bar=baz#fragment', '/foo/bar?bar=baz#fragment'],
            ['/foo/bar/?bar=baz#fragment', '/foo/bar?bar=baz#fragment'],
        ];
    }

    #[Test]
    public function it_can_remove_query_and_fragment()
    {
        $this->assertEquals(null, URL::removeQueryAndFragment(null));

        $this->assertEquals('https://example.com', URL::removeQueryAndFragment('https://example.com?query'));
        $this->assertEquals('https://example.com', URL::removeQueryAndFragment('https://example.com#anchor'));
        $this->assertEquals('https://example.com', URL::removeQueryAndFragment('https://example.com?foo=bar&baz=qux'));
        $this->assertEquals('https://example.com', URL::removeQueryAndFragment('https://example.com?foo=bar&baz=qux#anchor'));

        $this->assertEquals('https://example.com/', URL::removeQueryAndFragment('https://example.com/?query'));
        $this->assertEquals('https://example.com/', URL::removeQueryAndFragment('https://example.com/#anchor'));
        $this->assertEquals('https://example.com/', URL::removeQueryAndFragment('https://example.com/?foo=bar&baz=qux'));
        $this->assertEquals('https://example.com/', URL::removeQueryAndFragment('https://example.com/?foo=bar&baz=qux#anchor'));

        $this->assertEquals('https://example.com/about', URL::removeQueryAndFragment('https://example.com/about?query'));
        $this->assertEquals('https://example.com/about', URL::removeQueryAndFragment('https://example.com/about#anchor'));
        $this->assertEquals('https://example.com/about', URL::removeQueryAndFragment('https://example.com/about?foo=bar&baz=qux'));
        $this->assertEquals('https://example.com/about', URL::removeQueryAndFragment('https://example.com/about?foo=bar&baz=qux#anchor'));
    }

    #[Test]
    #[DataProvider('enforceTrailingSlashesProvider')]
    public function enforces_trailing_slashes($url, $expected)
    {
        URL::enforceTrailingSlashes();

        $this->assertSame($expected, URL::normalizeTrailingSlash($url));
        $this->assertSame($expected, URL::tidy($url));
    }

    public static function enforceTrailingSlashesProvider()
    {
        return [
            ['', '/'],
            ['/', '/'],

            ['?query', '/?query'],
            ['#anchor', '/#anchor'],
            ['?foo=bar&baz=qux', '/?foo=bar&baz=qux'],
            ['?foo=bar&baz=qux#anchor', '/?foo=bar&baz=qux#anchor'],

            ['/?query', '/?query'],
            ['/#anchor', '/#anchor'],
            ['/?foo=bar&baz=qux', '/?foo=bar&baz=qux'],
            ['/?foo=bar&baz=qux#anchor', '/?foo=bar&baz=qux#anchor'],

            ['/about?query', '/about/?query'],
            ['/about#anchor', '/about/#anchor'],
            ['/about?foo=bar&baz=qux', '/about/?foo=bar&baz=qux'],
            ['/about?foo=bar&baz=qux#anchor', '/about/?foo=bar&baz=qux#anchor'],

            ['/about/?query', '/about/?query'],
            ['/about/#anchor', '/about/#anchor'],
            ['/about/?foo=bar&baz=qux', '/about/?foo=bar&baz=qux'],
            ['/about/?foo=bar&baz=qux#anchor', '/about/?foo=bar&baz=qux#anchor'],

            ['https://example.com?query', 'https://example.com/?query'],
            ['https://example.com#anchor', 'https://example.com/#anchor'],
            ['https://example.com?foo=bar&baz=qux', 'https://example.com/?foo=bar&baz=qux'],
            ['https://example.com?foo=bar&baz=qux#anchor', 'https://example.com/?foo=bar&baz=qux#anchor'],

            ['https://example.com/?query', 'https://example.com/?query'],
            ['https://example.com/#anchor', 'https://example.com/#anchor'],
            ['https://example.com/?foo=bar&baz=qux', 'https://example.com/?foo=bar&baz=qux'],
            ['https://example.com/?foo=bar&baz=qux#anchor', 'https://example.com/?foo=bar&baz=qux#anchor'],

            ['https://example.com/about?query', 'https://example.com/about/?query'],
            ['https://example.com/about#anchor', 'https://example.com/about/#anchor'],
            ['https://example.com/about?foo=bar&baz=qux', 'https://example.com/about/?foo=bar&baz=qux'],
            ['https://example.com/about?foo=bar&baz=qux#anchor', 'https://example.com/about/?foo=bar&baz=qux#anchor'],

            ['https://example.com/about/?query', 'https://example.com/about/?query'],
            ['https://example.com/about/#anchor', 'https://example.com/about/#anchor'],
            ['https://example.com/about/?foo=bar&baz=qux', 'https://example.com/about/?foo=bar&baz=qux'],
            ['https://example.com/about/?foo=bar&baz=qux#anchor', 'https://example.com/about/?foo=bar&baz=qux#anchor'],
        ];
    }

    #[Test]
    #[DataProvider('removeTrailingSlashesProvider')]
    public function removes_trailing_slashes($url, $expected)
    {
        $this->assertSame($expected, URL::normalizeTrailingSlash($url));
        $this->assertSame($expected, URL::tidy($url));
    }

    public static function removeTrailingSlashesProvider()
    {
        return [
            ['', '/'],
            ['/', '/'],

            ['?query', '/?query'],
            ['#anchor', '/#anchor'],
            ['?foo=bar&baz=qux', '/?foo=bar&baz=qux'],
            ['?foo=bar&baz=qux#anchor', '/?foo=bar&baz=qux#anchor'],

            ['/?query', '/?query'],
            ['/#anchor', '/#anchor'],
            ['/?foo=bar&baz=qux', '/?foo=bar&baz=qux'],
            ['/?foo=bar&baz=qux#anchor', '/?foo=bar&baz=qux#anchor'],

            ['/about?query', '/about?query'],
            ['/about#anchor', '/about#anchor'],
            ['/about?foo=bar&baz=qux', '/about?foo=bar&baz=qux'],
            ['/about?foo=bar&baz=qux#anchor', '/about?foo=bar&baz=qux#anchor'],

            ['/about/?query', '/about?query'],
            ['/about/#anchor', '/about#anchor'],
            ['/about/?foo=bar&baz=qux', '/about?foo=bar&baz=qux'],
            ['/about/?foo=bar&baz=qux#anchor', '/about?foo=bar&baz=qux#anchor'],

            ['https://example.com?query', 'https://example.com?query'],
            ['https://example.com#anchor', 'https://example.com#anchor'],
            ['https://example.com?foo=bar&baz=qux', 'https://example.com?foo=bar&baz=qux'],
            ['https://example.com?foo=bar&baz=qux#anchor', 'https://example.com?foo=bar&baz=qux#anchor'],

            ['https://example.com/?query', 'https://example.com?query'],
            ['https://example.com/#anchor', 'https://example.com#anchor'],
            ['https://example.com/?foo=bar&baz=qux', 'https://example.com?foo=bar&baz=qux'],
            ['https://example.com/?foo=bar&baz=qux#anchor', 'https://example.com?foo=bar&baz=qux#anchor'],

            ['https://example.com/about?query', 'https://example.com/about?query'],
            ['https://example.com/about#anchor', 'https://example.com/about#anchor'],
            ['https://example.com/about?foo=bar&baz=qux', 'https://example.com/about?foo=bar&baz=qux'],
            ['https://example.com/about?foo=bar&baz=qux#anchor', 'https://example.com/about?foo=bar&baz=qux#anchor'],

            ['https://example.com/about/?query', 'https://example.com/about?query'],
            ['https://example.com/about/#anchor', 'https://example.com/about#anchor'],
            ['https://example.com/about/?foo=bar&baz=qux', 'https://example.com/about?foo=bar&baz=qux'],
            ['https://example.com/about/?foo=bar&baz=qux#anchor', 'https://example.com/about?foo=bar&baz=qux#anchor'],
        ];
    }

    #[Test]
    public function it_can_configure_and_unconfigure_enforcing_of_trailing_slashes()
    {
        $this->assertSame('https://example.com?query', URL::normalizeTrailingSlash('https://example.com?query'));
        $this->assertSame('https://example.com?query', URL::tidy('https://example.com?query'));
        $this->assertSame('https://example.com/foo', URL::parent('https://example.com/foo/bar'));
        $this->assertSame('http://localhost/foo', URL::prependSiteUrl('/foo'));
        $this->assertSame('/foo', URL::removeSiteUrl('http://localhost/foo'));
        $this->assertSame('http://absolute-url-resolved-from-request.com/foo?query', URL::makeAbsolute('/foo?query'));
        $this->assertSame('/foo?query', URL::makeRelative('https://example.com/foo?query'));
        $this->assertSame('https://example.com/bar?query', URL::assemble('https://example.com', 'bar', '?query'));
        $this->assertSame('https://example.com/bar', URL::replaceSlug('https://example.com/foo', 'bar'));

        URL::enforceTrailingSlashes();

        $this->assertSame('https://example.com/?query', URL::normalizeTrailingSlash('https://example.com?query'));
        $this->assertSame('https://example.com/?query', URL::tidy('https://example.com?query'));
        $this->assertSame('https://example.com/foo/', URL::parent('https://example.com/foo/bar'));
        $this->assertSame('http://localhost/foo/', URL::prependSiteUrl('/foo'));
        $this->assertSame('/foo/', URL::removeSiteUrl('http://localhost/foo'));
        $this->assertSame('http://absolute-url-resolved-from-request.com/foo/?query', URL::makeAbsolute('/foo?query'));
        $this->assertSame('/foo/?query', URL::makeRelative('https://example.com/foo?query'));
        $this->assertSame('https://example.com/bar/?query', URL::assemble('https://example.com', 'bar', '?query'));
        $this->assertSame('https://example.com/bar/', URL::replaceSlug('https://example.com/foo', 'bar'));

        URL::enforceTrailingSlashes(false);

        $this->assertSame('https://example.com?query', URL::normalizeTrailingSlash('https://example.com?query'));
        $this->assertSame('https://example.com?query', URL::tidy('https://example.com?query'));
        $this->assertSame('https://example.com/foo', URL::parent('https://example.com/foo/bar'));
        $this->assertSame('http://localhost/foo', URL::prependSiteUrl('/foo'));
        $this->assertSame('/foo', URL::removeSiteUrl('http://localhost/foo'));
        $this->assertSame('http://absolute-url-resolved-from-request.com/foo?query', URL::makeAbsolute('/foo?query'));
        $this->assertSame('/foo?query', URL::makeRelative('https://example.com/foo?query'));
        $this->assertSame('https://example.com/bar?query', URL::assemble('https://example.com', 'bar', '?query'));
        $this->assertSame('https://example.com/bar', URL::replaceSlug('https://example.com/foo', 'bar'));
    }
}
