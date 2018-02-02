<?php

namespace Tests\Sites;

use Tests\TestCase;
use Statamic\Sites\Site;
use Statamic\Sites\Sites;

class SiteTest extends TestCase
{
    /** @test */
    function gets_handle()
    {
        $site = new Site('en', []);

        $this->assertEquals('en', $site->handle());
    }

    /** @test */
    function gets_url()
    {
        $site = new Site('en', ['url' => 'http://test.com/']);

        $this->assertEquals('http://test.com/', $site->url());
    }

    /** @test */
    function gets_path()
    {
        tap(new Site('en', ['url' => 'http://test.com/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/foo'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/foo/bar'));
        });

        tap(new Site('fr', ['url' => 'http://test.com/fr/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://test.com/fr/'));
            $this->assertEquals('/foo', $site->relativePath('http://test.com/fr/foo'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://test.com/fr/foo/bar'));
        });

        tap(new Site('sub', ['url' => 'http://subdomain.test.com/']), function ($site) {
            $this->assertEquals('/', $site->relativePath('http://subdomain.test.com/'));
            $this->assertEquals('/foo', $site->relativePath('http://subdomain.test.com/foo'));
            $this->assertEquals('/foo/bar', $site->relativePath('http://subdomain.test.com/foo/bar'));
        });
    }
}