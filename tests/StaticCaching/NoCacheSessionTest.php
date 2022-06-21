<?php

namespace Tests\StaticCaching;

use Illuminate\Support\Facades\Cache;
use Mockery;
use Statamic\StaticCaching\NoCache\CacheSession;
use Tests\FakesContent;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

class NoCacheSessionTest extends TestCase
{
    use FakesContent;
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function when_pushing_a_section_it_will_filter_out_cascade()
    {
        $session = new CacheSession('/');

        $session->setCascade([
            'csrf_token' => 'abc',
            'now' => 'carbon',
            'title' => 'base title',
        ]);

        $session->pushSection('', [
            'csrf_token' => 'abc',
            'now' => 'carbon',
            'title' => 'different title',
            'foo' => 'bar',
            'baz' => 'qux',
        ], '');

        $this->assertEquals([
            'title' => 'different title',
            'foo' => 'bar',
            'baz' => 'qux',
        ], collect($session->getContexts())->first());
    }

    /** @test */
    public function it_gets_the_view_data()
    {
        // view data should be the context,
        // with the cascade merged in.

        $session = new CacheSession('/');

        $session->pushSection('', [
            'foo' => 'bar',
            'baz' => 'qux',
            'title' => 'local title',
        ], '');

        $section = collect($session->getContexts())->keys()->first();

        $session->setCascade([
            'csrf_token' => 'abc',
            'now' => 'carbon',
            'title' => 'root title',
        ]);

        $this->assertEquals([
            'csrf_token' => 'abc',
            'now' => 'carbon',
            'foo' => 'bar',
            'baz' => 'qux',
            'title' => 'local title',
        ], $session->getViewData($section));
    }

    /** @test */
    public function it_writes()
    {
        // Testing that the cache key used is unique to the url.
        // The contents aren't really important.

        Cache::shouldReceive('forever')
            ->with('nocache::session.'.md5('/'), Mockery::any())
            ->once();

        Cache::shouldReceive('forever')
            ->with('nocache::session.'.md5('/foo'), Mockery::any())
            ->once();

        (new CacheSession('/'))->write();
        (new CacheSession('/foo'))->write();
    }

    /** @test */
    public function it_restores_from_cache()
    {
        Cache::forever('nocache::session.'.md5('http://localhost/test'), [
            'contexts' => ['foo' => 'bar'],
            'sections' => ['baz' => 'qux'],
        ]);

        $this->createPage('/test', [
            'with' => ['title' => 'Test page'],
        ]);

        $session = new CacheSession('http://localhost/test');
        $this->assertEquals([], $session->getContexts());
        $this->assertEquals([], $session->getSections());
        $this->assertEquals([], $session->getCascade());

        $session->restore();

        $this->assertEquals(['foo' => 'bar'], $session->getContexts());
        $this->assertEquals(['baz' => 'qux'], $session->getSections());
        $this->assertNotEquals([], $cascade = $session->getCascade());
        $this->assertEquals('/test', $cascade['url']);
        $this->assertEquals('Test page', $cascade['title']);
        $this->assertEquals('http://localhost/cp', $cascade['cp_url']);
    }

    /** @test */
    public function a_singleton_is_bound_in_the_container()
    {
        $this->get('/test?foo=bar');

        $session = $this->app->make(CacheSession::class);

        $this->assertInstanceOf(CacheSession::class, $session);
        $this->assertEquals('http://localhost/test?foo=bar', $session->getUrl());
    }
}
