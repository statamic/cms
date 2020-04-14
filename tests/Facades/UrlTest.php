<?php

namespace Tests\Facades;

use Tests\TestCase;
use Statamic\Support\Str;
use Statamic\Facades\URL;
use Statamic\Facades\Site;

class UrlTest extends TestCase
{
    public function testBuildsUrl()
    {
        $url = URL::buildFromPath('pages/about/index.md');
        $this->assertEquals('/about', $url);
    }

    public function testBuildsHomepage()
    {
        $url = URL::buildFromPath('pages/index.md');
        $this->assertEquals('/', $url);
    }

    public function testBuildsUrlFromFullPath()
    {
        $url = URL::buildFromPath(base_path() . '/pages/index.md');
        $this->assertEquals('/', $url);
    }

    public function testBuildsLocalizedUrl()
    {
        $url = URL::buildFromPath('pages/about/fr.index.md');
        $this->assertEquals('/about', $url);
    }

    public function testBuildsLocalizedHomepage()
    {
        $url = URL::buildFromPath('pages/fr.index.md');
        $this->assertEquals('/', $url);
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
    }
}
