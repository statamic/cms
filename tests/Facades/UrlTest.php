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
        URL::clearUrlCache();

        parent::tearDown();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('app.url', 'http://absolute-url-resolved-from-request.com');
    }

    #[Test]
    #[DataProvider('tidyProvider')]
    public function it_can_tidy_urls($url, $expected)
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        $this->assertSame($expected, URL::tidy($url));

        URL::enforceTrailingSlashes();

        if (! Str::contains($url, 'external-site.com')) {
            $expected = preg_replace('/this-site\.com$/', 'this-site.com/', $expected);
            $expected = str_replace('page', 'page/', $expected);
        }

        $this->assertSame($expected, URL::tidy($url));
    }

    public static function tidyProvider()
    {
        return [
            'null case tidies to relative homepage' => [null, '/'],
            'relative homepage' => ['/', '/'],
            'relative homepage enforce slash' => ['', '/'],

            'relative route' => ['/page', '/page'],
            'relative route enforce leading slash' => ['page', '/page'],
            'relative route normalizes trailing slash' => ['/page/', '/page'],
            'relative nested route enforce leading slash' => ['foo/page', '/foo/page'],
            'relative nested route enforce leading slash with query param' => ['foo/page?query', '/foo/page?query'],
            'relative nested route enforce leading slash with anchor fragment' => ['foo/page#anchor', '/foo/page#anchor'],
            'relative nested route enforce leading slash with qauery and anchor fragment' => ['foo/page?query#anchor', '/foo/page?query#anchor'],
            'relative nested route normalizes trailing slash' => ['/foo/page/', '/foo/page'],
            'relative nested route normalizes trailing slash with query param' => ['/foo/page/?query', '/foo/page?query'],
            'relative nested route normalizes trailing slash with anchor fragment' => ['/foo/page/#anchor', '/foo/page#anchor'],
            'relative nested route normalizes trailing slash with query and anchor fragment' => ['/foo/page/?query#anchor', '/foo/page?query#anchor'],

            'absolute url homepage' => ['http://this-site.com', 'http://this-site.com'],
            'absolute url route' => ['http://this-site.com/page', 'http://this-site.com/page'],
            'absolute url query' => ['http://this-site.com/page?query', 'http://this-site.com/page?query'],
            'absolute url anchor' => ['http://this-site.com/page#anchor', 'http://this-site.com/page#anchor'],
            'absolute url homepage normalizes trailing slash' => ['http://this-site.com/', 'http://this-site.com'],
            'absolute url route normalizes trailing slash' => ['http://this-site.com/page/', 'http://this-site.com/page'],
            'absolute url query normalizes trailing slash' => ['http://this-site.com/page/?query', 'http://this-site.com/page?query'],
            'absolute url anchor normalizes trailing slash' => ['http://this-site.com/page/#anchor', 'http://this-site.com/page#anchor'],

            'fix multiple slashes' => ['////foo///bar////page', '/foo/bar/page'],
            'fix multiple slashes and enforce leading slash' => ['foo///bar////page', '/foo/bar/page'],
            'fix multiple slashes and normalize trailing slash' => ['////foo///bar////page///', '/foo/bar/page'],
            'fixing multiple slashes on absolute url tidies to double slash protocol' => ['http:////this-site.com/foo///bar////page', 'http://this-site.com/foo/bar/page'],
            'fixing multiple slashes on external url tidies to double slash protocol' => ['http:////external-site.com/foo///bar////page', 'http://external-site.com/foo/bar/page'],

            'external url doesnt touch trailing slash' => ['http://external-site.com/', 'http://external-site.com/'],
            'external nested url doesnt touch trailing slash' => ['http://external-site.com/page/', 'http://external-site.com/page/'],
            'external nested url doesnt touch trailing slash or query fragment' => ['http://external-site.com/page/?query#fragment', 'http://external-site.com/page/?query#fragment'],
        ];
    }

    #[Test]
    public function it_can_force_tidy_unconfigured_external_urls()
    {
        $this->assertSame('http://external.com/', URL::tidy('http://external.com/'));
        $this->assertSame('http://external.com', URL::tidy('http://external.com/', force: true));

        URL::enforceTrailingSlashes();

        $this->assertSame('http://external.com', URL::tidy('http://external.com'));
        $this->assertSame('http://external.com/', URL::tidy('http://external.com', force: true));
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
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        $this->assertEquals('/', URL::removeSiteUrl('http://this-site.com'));
        $this->assertEquals('/foo', URL::removeSiteUrl('http://this-site.com/foo'));
        $this->assertEquals('/foo', URL::removeSiteUrl('http://this-site.com/foo/'));
        $this->assertEquals('http://external-site.com/foo/', URL::removeSiteUrl('http://external-site.com/foo/'));

        URL::enforceTrailingSlashes();

        $this->assertEquals('/', URL::removeSiteUrl('http://this-site.com/'));
        $this->assertEquals('/foo/', URL::removeSiteUrl('http://this-site.com/foo'));
        $this->assertEquals('/foo/', URL::removeSiteUrl('http://this-site.com/foo/'));
        $this->assertEquals('http://external-site.com/foo', URL::removeSiteUrl('http://external-site.com/foo'));
    }

    #[Test]
    public function it_determines_absolute_url()
    {
        $this->assertTrue(URL::isAbsolute('http://example.com'));
        $this->assertTrue(URL::isAbsolute('http://example.com/'));
        $this->assertTrue(URL::isAbsolute('http://example.com/some-slug'));
        $this->assertTrue(URL::isAbsolute('http://example.com/some-slug?foo'));
        $this->assertTrue(URL::isAbsolute('http://example.com/some-slug#anchor'));
        $this->assertTrue(URL::isAbsolute('http://example.com'));
        $this->assertTrue(URL::isAbsolute('http://example.com/'));
        $this->assertTrue(URL::isAbsolute('http://example.com/some-slug'));
        $this->assertFalse(URL::isAbsolute('/'));
        $this->assertFalse(URL::isAbsolute('/foo'));
        $this->assertFalse(URL::isAbsolute('/foo/bar?query'));
        $this->assertFalse(URL::isAbsolute('foo'));
        $this->assertFalse(URL::isAbsolute('image.png'));
        $this->assertFalse(URL::isAbsolute('?query'));
        $this->assertFalse(URL::isAbsolute('#anchor'));
        $this->assertFalse(URL::isAbsolute(''));
        $this->assertFalse(URL::isAbsolute(null));
    }

    #[Test]
    public function it_determines_external_url()
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        $this->assertTrue(URL::isExternal('http://external-site.com'));
        $this->assertTrue(URL::isExternal('http://external-site.com/'));
        $this->assertTrue(URL::isExternal('http://external-site.com/some-slug'));
        $this->assertTrue(URL::isExternal('http://external-site.com/some-slug?foo'));
        $this->assertTrue(URL::isExternal('http://external-site.com/some-slug#anchor'));
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
        $this->assertTrue(URL::isExternal('http://external-site.com'));
        $this->assertTrue(URL::isExternal('http://external-site.com/'));
        $this->assertTrue(URL::isExternal('http://external-site.com/some-slug'));
        $this->assertTrue(URL::isExternal('http://external-site.com/some-slug?foo'));
        $this->assertTrue(URL::isExternal('http://external-site.com/some-slug#anchor'));
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

        $this->assertFalse(URL::isExternalToApplication('http://absolute-url-resolved-from-request.com'));
        $this->assertFalse(URL::isExternalToApplication('http://absolute-url-resolved-from-request.com/'));
        $this->assertFalse(URL::isExternalToApplication('http://absolute-url-resolved-from-request.com/some-slug'));
        $this->assertFalse(URL::isExternalToApplication('/foo'));
        $this->assertFalse(URL::isExternalToApplication('#anchor'));
        $this->assertFalse(URL::isExternalToApplication(''));
        $this->assertFalse(URL::isExternalToApplication(null));
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
        $this->setSites([
            'en' => ['url' => 'http://this-site.com/', 'locale' => 'en_US', 'name' => 'English'],
            'fr' => ['url' => 'https://secure-site.com/', 'locale' => 'fr_FR', 'name' => 'French'],
        ]);

        $this->assertSame($parent, URL::parent($child));

        URL::enforceTrailingSlashes();

        $this->assertSame(Str::ensureRight($parent, '/'), URL::parent($child));
    }

    public static function parentProvider()
    {
        return [
            'relative homepage to homepage' => ['/', '/'],
            'absolute homepage to homepage' => ['http://this-site.com', 'http://this-site.com'],
            'absolute homepage to homepage with trailing slash' => ['http://this-site.com/', 'http://this-site.com'],

            'relative route to parent homepage' => ['/foo', '/'],
            'relative route to parent homepage with trailing slash' => ['/foo/', '/'],
            'absolute route to parent homepage' => ['http://this-site.com/foo', 'http://this-site.com'],
            'absolute route to parent homepage with trailing slash' => ['http://this-site.com/foo/', 'http://this-site.com'],

            'relative nested route to parent homepage' => ['/foo/bar', '/foo'],
            'relative nested route to parent homepage with trailing slash' => ['/foo/bar/', '/foo'],
            'absolute nested route to parent homepage' => ['http://this-site.com/foo/bar', 'http://this-site.com/foo'],
            'absolute nested route to parent homepage with trailing slash' => ['http://this-site.com/foo/bar/', 'http://this-site.com/foo'],

            'removes query from relative url' => ['/?alpha', '/'],
            'removes query from absolute url' => ['http://this-site.com/?alpha', 'http://this-site.com'],
            'removes anchor fragment from relative url' => ['/#alpha', '/'],
            'removes anchor fragment from absolute url' => ['http://this-site.com/#alpha', 'http://this-site.com'],
            'removes query and anchor fragment from relative url' => ['/?alpha#beta', '/'],
            'removes query and anchor fragment from absolute url' => ['http://this-site.com/?alpha#beta', 'http://this-site.com'],

            'preserves scheme and host' => ['https://secure-site.com/foo/bar/', 'https://secure-site.com/foo'],

            // TODO...
            // 'preserves lack of trailing slash on external site' => ['https://secure-site.com/foo/bar', 'https://secure-site.com/foo'],
            // 'preserves trailing slash on external site' => ['https://secure-site.com/foo/bar/', 'https://secure-site.com/foo/'],
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
    #[DataProvider('absoluteProvider')]
    public function it_makes_urls_absolute($url, $expected, $forceScheme = false)
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        if ($forceScheme) {
            \Illuminate\Support\Facades\URL::forceScheme($forceScheme);
        }

        $this->assertSame($expected, URL::makeAbsolute($url));

        URL::enforceTrailingSlashes();

        $expected = Str::contains($url, 'external-site.com')
            ? $url
            : Str::ensureRight($expected, '/');

        $this->assertSame($expected, URL::makeAbsolute($url));
    }

    public static function absoluteProvider()
    {
        return [
            ['http://external-site.com', 'http://external-site.com'], // external absolute url provided, so url is left alone.
            ['http://external-site.com/', 'http://external-site.com/'], // external absolute url provided, so url is left alone.
            ['http://this-site.com/foo/', 'http://this-site.com/foo'], // already absolute, but we can still normalize trailing slashes
            ['/', 'http://absolute-url-resolved-from-request.com'],
            ['foo', 'http://absolute-url-resolved-from-request.com/foo'],
            ['/foo', 'http://absolute-url-resolved-from-request.com/foo'],
            ['/foo/', 'http://absolute-url-resolved-from-request.com/foo'],

            ['http://external-site.com', 'http://external-site.com', 'https'], // external absolute url provided, so scheme and trailing slash are left alone.
            ['http://external-site.com/', 'http://external-site.com/', 'https'], // external absolute url provided, so scheme and trailing slash are left alone.
            ['/', 'https://absolute-url-resolved-from-request.com', 'https'],
            ['foo', 'https://absolute-url-resolved-from-request.com/foo', 'https'],
            ['/foo', 'https://absolute-url-resolved-from-request.com/foo', 'https'],
            ['/foo/', 'https://absolute-url-resolved-from-request.com/foo', 'https'],

            ['https://external-site.com', 'https://external-site.com', 'http'], // external absolute url provided, so scheme and trailing slash are left alone.
            ['https://external-site.com/', 'https://external-site.com/', 'http'], // external absolute url provided, so scheme and trailing slash are left alone.
            ['/', 'http://absolute-url-resolved-from-request.com', 'http'],
            ['foo', 'http://absolute-url-resolved-from-request.com/foo', 'http'],
            ['/foo', 'http://absolute-url-resolved-from-request.com/foo', 'http'],
            ['/foo/', 'http://absolute-url-resolved-from-request.com/foo', 'http'],
        ];
    }

    #[Test]
    #[DataProvider('relativeProvider')]
    public function it_makes_urls_relative($url, $expected)
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
            ['foo', '/foo'],
            ['foo/bar', '/foo/bar'],

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
    #[DataProvider('encodeProvider')]
    public function it_can_encode_urls($url, $expected)
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        $this->assertSame($expected, URL::encode($url));

        URL::enforceTrailingSlashes();

        $expected = str_replace('page', 'page/', $expected);

        $this->assertSame($expected, URL::encode($url));
    }

    public static function encodeProvider()
    {
        $encodable = '\'"$^<>[](){}';
        $encoded = '%27%22%24%5E%3C%3E%5B%5D%28%29%7B%7D';

        return [
            'null case tidies to relative homepage' => [null, '/'],
            'relative homepage' => ['/', '/'],
            'relative homepage enforce slash' => ['', '/'],

            'relative route with encodable' => ["/{$encodable}/page", "/{$encoded}/page"],
            'relative route with encodable enforce leading slash' => ["{$encodable}/page", "/{$encoded}/page"],
            'relative route with encodable normalize trailing slash' => ["/{$encodable}/page/", "/{$encoded}/page"],

            'doesnt encode specific characters' => ['http://this-site.com/page?param&characters=-/@:;,+!*|%#fragment', 'http://this-site.com/page?param&characters=-/@:;,+!*|%#fragment'],
            'doesnt encode specific characters but still can normalize trailing slash' => ['http://this-site.com/page/?param&characters=-/@:;,+!*|%#fragment', 'http://this-site.com/page?param&characters=-/@:;,+!*|%#fragment'],

            'absolute external url doesnt enforce trailing slash' => ['http://external-site.com.com/foo?param&characters=-/@:;,+!*|%#fragment', 'http://external-site.com.com/foo?param&characters=-/@:;,+!*|%#fragment'],
            'absolute external url doesnt remove trailing slash' => ['http://external-site.com.com/foo/?param&characters=-/@:;,+!*|%#fragment', 'http://external-site.com.com/foo/?param&characters=-/@:;,+!*|%#fragment'],
        ];
    }

    #[Test]
    public function it_can_get_gravatar_image_urls_from_email()
    {
        $hashGravatarEmail = function ($email) {
            return e(md5(strtolower($email)));
        };

        $this->assertSame(
            'https://www.gravatar.com/avatar/'.$hashGravatarEmail('Jeremy@pearl.jam'),
            URL::gravatar('Jeremy@pearl.jam'),
        );

        $this->assertSame(
            'https://www.gravatar.com/avatar/'.$hashGravatarEmail('Jeremy@pearl.jam').'?s=32',
            URL::gravatar('Jeremy@pearl.jam', 32),
        );
    }

    #[Test]
    #[DataProvider('removeQueryAndFragmentProvider')]
    public function it_can_remove_query_and_fragment($url, $expected)
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        $this->assertSame($expected, URL::removeQueryAndFragment($url));

        URL::enforceTrailingSlashes();

        $expected = preg_replace('/this-site\.com$/', 'this-site.com/', $expected);
        $expected = str_replace('page', 'page/', $expected);

        $this->assertSame($expected, URL::removeQueryAndFragment($url));
    }

    public static function removeQueryAndFragmentProvider()
    {
        return [
            'null case tidies to relative homepage' => [null, '/'],

            'relative homepage with query param' => ['/?query', '/'],
            'relative homepage with anchor fragment' => ['/#anchor', '/'],
            'relative homepage with query and anchor fragment' => ['/?query#anchor', '/'],
            'relative homepage enforce slash with query param' => ['?query', '/'],
            'relative homepage enforce slash with anchor fragment' => ['#anchor', '/'],
            'relative homepage enforce slash with query and anchor fragment' => ['?query#anchor', '/'],

            'relative route enforce leading slash with query param' => ['foo/page?query', '/foo/page'],
            'relative route enforce leading slash with anchor fragment' => ['foo/page#anchor', '/foo/page'],
            'relative route enforce leading slash with query and anchor fragment' => ['foo/page?query#anchor', '/foo/page'],
            'relative route normalizes trailing slash with query param' => ['/foo/page/?query', '/foo/page'],
            'relative route normalizes trailing slash with anchor fragment' => ['/foo/page/#anchor', '/foo/page'],
            'relative route normalizes trailing slash with query and anchor fragment' => ['/foo/page/?query#anchor', '/foo/page'],

            'absolute url query' => ['http://this-site.com/page?query', 'http://this-site.com/page'],
            'absolute url anchor' => ['http://this-site.com/page#anchor', 'http://this-site.com/page'],
            'absolute url query normalizes trailing slash' => ['http://this-site.com/page/?query', 'http://this-site.com/page'],
            'absolute url anchor normalizes trailing slash' => ['http://this-site.com/page/#anchor', 'http://this-site.com/page'],

            'absolute external url query doesnt enforce trailing slash' => ['http://external-site.com/foo?query', 'http://external-site.com/foo'],
            'absolute external url anchor doesnt enforce trailing slash' => ['http://external-site.com/foo#anchor', 'http://external-site.com/foo'],
            'absolute external url query doesnt remove trailing slash' => ['http://external-site.com/foo/?query', 'http://external-site.com/foo/'],
            'absolute external url anchor doesnt remove trailing slash' => ['http://external-site.com/foo/#anchor', 'http://external-site.com/foo/'],
        ];
    }

    #[Test]
    #[DataProvider('enforceTrailingSlashesProvider')]
    public function enforces_trailing_slashes($url, $expected)
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        URL::enforceTrailingSlashes();

        $this->assertSame($expected, URL::tidy($url));
    }

    public static function enforceTrailingSlashesProvider()
    {
        return [
            [null, '/'],
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

            ['/about', '/about/'],
            ['/about?query', '/about/?query'],
            ['/about#anchor', '/about/#anchor'],
            ['/about?foo=bar&baz=qux', '/about/?foo=bar&baz=qux'],
            ['/about?foo=bar&baz=qux#anchor', '/about/?foo=bar&baz=qux#anchor'],

            ['/about/', '/about/'],
            ['/about/?query', '/about/?query'],
            ['/about/#anchor', '/about/#anchor'],
            ['/about/?foo=bar&baz=qux', '/about/?foo=bar&baz=qux'],
            ['/about/?foo=bar&baz=qux#anchor', '/about/?foo=bar&baz=qux#anchor'],

            ['http://this-site.com', 'http://this-site.com/'],
            ['http://this-site.com?query', 'http://this-site.com/?query'],
            ['http://this-site.com#anchor', 'http://this-site.com/#anchor'],
            ['http://this-site.com?foo=bar&baz=qux', 'http://this-site.com/?foo=bar&baz=qux'],
            ['http://this-site.com?foo=bar&baz=qux#anchor', 'http://this-site.com/?foo=bar&baz=qux#anchor'],

            ['http://this-site.com/', 'http://this-site.com/'],
            ['http://this-site.com/?query', 'http://this-site.com/?query'],
            ['http://this-site.com/#anchor', 'http://this-site.com/#anchor'],
            ['http://this-site.com/?foo=bar&baz=qux', 'http://this-site.com/?foo=bar&baz=qux'],
            ['http://this-site.com/?foo=bar&baz=qux#anchor', 'http://this-site.com/?foo=bar&baz=qux#anchor'],

            ['http://this-site.com/about', 'http://this-site.com/about/'],
            ['http://this-site.com/about?query', 'http://this-site.com/about/?query'],
            ['http://this-site.com/about#anchor', 'http://this-site.com/about/#anchor'],
            ['http://this-site.com/about?foo=bar&baz=qux', 'http://this-site.com/about/?foo=bar&baz=qux'],
            ['http://this-site.com/about?foo=bar&baz=qux#anchor', 'http://this-site.com/about/?foo=bar&baz=qux#anchor'],

            ['http://this-site.com/about/', 'http://this-site.com/about/'],
            ['http://this-site.com/about/?query', 'http://this-site.com/about/?query'],
            ['http://this-site.com/about/#anchor', 'http://this-site.com/about/#anchor'],
            ['http://this-site.com/about/?foo=bar&baz=qux', 'http://this-site.com/about/?foo=bar&baz=qux'],
            ['http://this-site.com/about/?foo=bar&baz=qux#anchor', 'http://this-site.com/about/?foo=bar&baz=qux#anchor'],

            ['http://external-site.com', 'http://external-site.com'],
            ['http://external-site.com/about', 'http://external-site.com/about'],
            ['http://external-site.com/about?query', 'http://external-site.com/about?query'],
            ['http://external-site.com/about#anchor', 'http://external-site.com/about#anchor'],
            ['http://external-site.com/about?query#anchor', 'http://external-site.com/about?query#anchor'],
        ];
    }

    #[Test]
    #[DataProvider('removeTrailingSlashesProvider')]
    public function removes_trailing_slashes($url, $expected)
    {
        $this->setSiteValue('en', 'url', 'http://this-site.com/');

        $this->assertSame($expected, URL::tidy($url));
    }

    public static function removeTrailingSlashesProvider()
    {
        return [
            [null, '/'],
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

            ['/about', '/about'],
            ['/about?query', '/about?query'],
            ['/about#anchor', '/about#anchor'],
            ['/about?foo=bar&baz=qux', '/about?foo=bar&baz=qux'],
            ['/about?foo=bar&baz=qux#anchor', '/about?foo=bar&baz=qux#anchor'],

            ['/about/', '/about'],
            ['/about/?query', '/about?query'],
            ['/about/#anchor', '/about#anchor'],
            ['/about/?foo=bar&baz=qux', '/about?foo=bar&baz=qux'],
            ['/about/?foo=bar&baz=qux#anchor', '/about?foo=bar&baz=qux#anchor'],

            ['http://this-site.com', 'http://this-site.com'],
            ['http://this-site.com?query', 'http://this-site.com?query'],
            ['http://this-site.com#anchor', 'http://this-site.com#anchor'],
            ['http://this-site.com?foo=bar&baz=qux', 'http://this-site.com?foo=bar&baz=qux'],
            ['http://this-site.com?foo=bar&baz=qux#anchor', 'http://this-site.com?foo=bar&baz=qux#anchor'],

            ['http://this-site.com/', 'http://this-site.com'],
            ['http://this-site.com/?query', 'http://this-site.com?query'],
            ['http://this-site.com/#anchor', 'http://this-site.com#anchor'],
            ['http://this-site.com/?foo=bar&baz=qux', 'http://this-site.com?foo=bar&baz=qux'],
            ['http://this-site.com/?foo=bar&baz=qux#anchor', 'http://this-site.com?foo=bar&baz=qux#anchor'],

            ['http://this-site.com/about', 'http://this-site.com/about'],
            ['http://this-site.com/about?query', 'http://this-site.com/about?query'],
            ['http://this-site.com/about#anchor', 'http://this-site.com/about#anchor'],
            ['http://this-site.com/about?foo=bar&baz=qux', 'http://this-site.com/about?foo=bar&baz=qux'],
            ['http://this-site.com/about?foo=bar&baz=qux#anchor', 'http://this-site.com/about?foo=bar&baz=qux#anchor'],

            ['http://this-site.com/about/', 'http://this-site.com/about'],
            ['http://this-site.com/about/?query', 'http://this-site.com/about?query'],
            ['http://this-site.com/about/#anchor', 'http://this-site.com/about#anchor'],
            ['http://this-site.com/about/?foo=bar&baz=qux', 'http://this-site.com/about?foo=bar&baz=qux'],
            ['http://this-site.com/about/?foo=bar&baz=qux#anchor', 'http://this-site.com/about?foo=bar&baz=qux#anchor'],

            ['http://external-site.com/', 'http://external-site.com/'],
            ['http://external-site.com/about/', 'http://external-site.com/about/'],
            ['http://external-site.com/about/?query', 'http://external-site.com/about/?query'],
            ['http://external-site.com/about/#anchor', 'http://external-site.com/about/#anchor'],
            ['http://external-site.com/about/?query#anchor', 'http://external-site.com/about/?query#anchor'],
        ];
    }

    #[Test]
    public function it_can_configure_and_unconfigure_enforcing_of_trailing_slashes()
    {
        $this->assertSame('http://localhost?query', URL::tidy('http://localhost/?query'));
        $this->assertSame('http://localhost/foo', URL::parent('http://localhost/foo/bar/'));
        $this->assertSame('http://localhost/foo', URL::prependSiteUrl('/foo/'));
        $this->assertSame('/foo', URL::removeSiteUrl('http://localhost/foo/'));
        $this->assertSame('http://absolute-url-resolved-from-request.com/foo?query', URL::makeAbsolute('/foo/?query'));
        $this->assertSame('/foo?query', URL::makeRelative('http://localhost/foo/?query'));
        $this->assertSame('http://localhost/bar?query', URL::assemble('http://localhost', 'bar', '?query'));
        $this->assertSame('http://localhost/bar', URL::replaceSlug('http://localhost/foo/', 'bar'));
        $this->assertSame('http://localhost/foo%24bar', URL::encode('http://localhost/foo$bar'));

        URL::enforceTrailingSlashes();

        $this->assertSame('http://localhost/?query', URL::tidy('http://localhost?query'));
        $this->assertSame('http://localhost/foo/', URL::parent('http://localhost/foo/bar'));
        $this->assertSame('http://localhost/foo/', URL::prependSiteUrl('/foo'));
        $this->assertSame('/foo/', URL::removeSiteUrl('http://localhost/foo'));
        $this->assertSame('http://absolute-url-resolved-from-request.com/foo/?query', URL::makeAbsolute('/foo?query'));
        $this->assertSame('/foo/?query', URL::makeRelative('http://localhost/foo?query'));
        $this->assertSame('http://localhost/bar/?query', URL::assemble('http://localhost', 'bar', '?query'));
        $this->assertSame('http://localhost/bar/', URL::replaceSlug('http://localhost/foo', 'bar'));
        $this->assertSame('http://localhost/foo%24bar/', URL::encode('http://localhost/foo$bar'));

        URL::enforceTrailingSlashes(false);

        $this->assertSame('http://localhost?query', URL::tidy('http://localhost/?query'));
        $this->assertSame('http://localhost/foo', URL::parent('http://localhost/foo/bar/'));
        $this->assertSame('http://localhost/foo', URL::prependSiteUrl('/foo/'));
        $this->assertSame('/foo', URL::removeSiteUrl('http://localhost/foo/'));
        $this->assertSame('http://absolute-url-resolved-from-request.com/foo?query', URL::makeAbsolute('/foo/?query'));
        $this->assertSame('/foo?query', URL::makeRelative('http://localhost/foo/?query'));
        $this->assertSame('http://localhost/bar?query', URL::assemble('http://localhost', 'bar', '?query'));
        $this->assertSame('http://localhost/bar', URL::replaceSlug('http://localhost/foo/', 'bar'));
        $this->assertSame('http://localhost/foo%24bar', URL::encode('http://localhost/foo$bar'));
    }
}
