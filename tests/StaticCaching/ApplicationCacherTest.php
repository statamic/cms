<?php

namespace Tests\StaticCaching;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Request;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Tests\TestCase;

class ApplicationCacherTest extends TestCase
{
    /** @test */
    public function gets_cached_page()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, []);
        $cache->forever('static-cache:responses:'.md5('http://example.com/test?foo=bar'), 'html content');
        $request = Request::create('http://example.com/test', 'GET', ['foo' => 'bar']);

        $this->assertEquals('html content', $cacher->getCachedPage($request));
    }

    /** @test */
    public function invalidating_a_url_removes_the_html_and_the_url()
    {
        $cache = app(Repository::class);
        $cacher = new ApplicationCacher($cache, ['base_url' => 'http://example.com']);
        $cache->forever('static-cache:'.md5('http://example.com').'.urls', [
            'one' => '/one', 'two' => '/two',
        ]);
        $cache->forever('static-cache:responses:one', 'html content');

        $cacher->invalidateUrl('/one');

        $this->assertEquals(['two' => '/two'], $cacher->getUrls()->all());
        $this->assertNull($cache->get('static-cache:responses:one'));
    }
}
