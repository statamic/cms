<?php

namespace Tests;

use Statamic\API\Config;
use Statamic\API\Str;
use Statamic\API\URL;

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
        Config::set('statamic.system.locales.en.url', 'http://site.com/');

        $this->assertEquals(
            'http://site.com/foo',
            URL::prependSiteUrl('/foo')
        );
    }

    public function testPrependsSiteUrlWithController()
    {
        Config::set('statamic.system.locales.en.url', 'http://site.com/index.php/');

        $this->assertEquals(
            'http://site.com/index.php/foo',
            URL::prependSiteUrl('/foo')
        );
    }

    public function testPrependsSiteUrlWithoutController()
    {
        // Override with what would be used on a normal request.
        request()->server->set('SCRIPT_NAME', '/index.php');

        Config::set('statamic.system.locales.en.url', 'http://site.com/index.php/');

        $this->assertEquals(
            'http://site.com/foo',
            URL::prependSiteUrl('/foo', null, false)
        );
    }

    public function testDeterminesExternalUrl()
    {
        Config::set('statamic.system.locales.en.url', 'http://this-site.com/');
        $this->assertTrue(URL::isExternalUrl('http://that-site.com'));
        $this->assertTrue(URL::isExternalUrl('http://that-site.com/'));
        $this->assertTrue(URL::isExternalUrl('http://that-site.com/some-slug'));
        $this->assertFalse(URL::isExternalUrl('http://this-site.com'));
        $this->assertFalse(URL::isExternalUrl('http://this-site.com/'));
        $this->assertFalse(URL::isExternalUrl('http://this-site.com/some-slug'));
    }
}
