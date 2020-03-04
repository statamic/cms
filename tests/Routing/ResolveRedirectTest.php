<?php

namespace Tests\Routing;

use Mockery;
use PHPUnit\Framework\TestCase;
use Statamic\Contracts\Entries\Entry;
use Statamic\Routing\ResolveRedirect;
use Statamic\Structures\Page;
use Statamic\Structures\Pages;

class ResolveRedirectTest extends TestCase
{
    /** @test */
    public function it_resolves_standard_redirects()
    {
        $resolver = new ResolveRedirect;

        $this->assertEquals('http://test.com', $resolver('http://test.com'));
        $this->assertEquals('https://test.com', $resolver('https://test.com'));
        $this->assertEquals('/test', $resolver('/test'));
        $this->assertEquals('test', $resolver('test'));
        $this->assertSame(404, $resolver('404'));
        $this->assertSame(404, $resolver(404));
        $this->assertSame('4-oh-4', $resolver('4-oh-4')); // strings with numbers won't become ints
        $this->assertNull($resolver(null));
    }

    /** @test */
    function it_cant_resolve_a_first_child_without_a_parent()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot resolve a page\'s child redirect without providing a page.');

        $resolver = new ResolveRedirect;

        $this->assertEquals('/page/child', $resolver('@child'));
    }

    /** @test */
    function it_cannot_resolve_a_first_child_redirect_if_the_parent_is_not_a_page()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot resolve a page\'s child redirect without providing a page.');

        $resolver = new ResolveRedirect;

        $this->assertEquals('/page/child', $resolver('@child', 'not a page object'));
    }

    /** @test */
    function it_resolves_first_child()
    {
        $resolver = new ResolveRedirect;

        $child = Mockery::mock(Page::class);
        $child->shouldReceive('url')->andReturn('/parent/first-child');

        $children = Mockery::mock(Pages::class);
        $children->shouldReceive('all')->andReturn(collect([$child]));

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('pages')->andReturn($children);

        $this->assertEquals('/parent/first-child', $resolver('@child', $parent));
    }

    /** @test */
    function it_resolves_first_child_through_an_entry()
    {
        $resolver = new ResolveRedirect;

        $child = Mockery::mock(Page::class);
        $child->shouldReceive('url')->andReturn('/parent/first-child');

        $children = Mockery::mock(Pages::class);
        $children->shouldReceive('all')->andReturn(collect([$child]));

        $parentPage = Mockery::mock(Page::class);
        $parentPage->shouldReceive('pages')->andReturn($children);

        $parent = Mockery::mock(Entry::class);
        $parent->shouldReceive('page')->andReturn($parentPage);

        $this->assertEquals('/parent/first-child', $resolver('@child', $parent));
    }

    /** @test */
    function a_parent_without_a_child_resolves_to_a_404()
    {
        $resolver = new ResolveRedirect;

        $pages = Mockery::mock(Pages::class);
        $pages->shouldReceive('all')->andReturn(collect([]));

        $parent = Mockery::mock(Page::class);
        $parent->shouldReceive('pages')->andReturn($pages);

        $this->assertEquals('404', $resolver('@child', $parent));
    }
}
