<?php

namespace Tests\Data;

use Tests\TestCase;
use Statamic\API\Page;
use Statamic\API\Config;

class PageTest extends TestCase
{
    /** @var  \Statamic\Data\Pages\Page */
    protected $page;

    public function setUp()
    {
        parent::setUp();

        $this->page = Page::create('/about')
            ->path('pages/about/index.md')
            ->with([
                'title' => 'Test',
                'foo' => 'bar',
            ])->get();
    }

    public function testGetsSlug()
    {
        $this->assertEquals('about', $this->page->slug());
    }

    public function testGetsPath()
    {
        $this->assertEquals('pages/about/index.md', $this->page->path());

        $this->page->unpublish();
        $this->assertEquals('pages/_about/index.md', $this->page->path());

        $this->page->publish();
        $this->assertEquals('pages/about/index.md', $this->page->path());
    }

    public function testGetsTemplate()
    {
        $this->assertEquals([null, 'default'], $this->page->template());

        $this->page->set('template', 'my-template');

        $this->assertEquals(['my-template', 'default'], $this->page->template());
    }

    public function testGetsLayout()
    {
        $this->assertEquals(config('theming.views.layout'), $this->page->layout());

        $this->page->set('layout', 'my-layout');

        $this->assertEquals('my-layout', $this->page->layout());
    }

    public function testGetsMount()
    {
        $this->assertFalse($this->page->hasEntries());

        $this->page->set('mount', 'blog');

        $this->assertTrue($this->page->hasEntries());
    }

    public function testGetsEntries()
    {
        $this->assertInstanceOf('Statamic\Data\Content\ContentCollection', $this->page->entries());

        $this->page->set('mount', 'blog');

        $this->assertInstanceOf('Statamic\Data\Content\ContentCollection', $this->page->entries());
    }

    public function testGetsEntriesFolder()
    {
        $this->assertNull($this->page->entriesCollection());

        $this->page->set('mount', 'blog');

        $this->assertEquals('blog', $this->page->entriesCollection());
    }

    public function testGetsChildren()
    {
        $this->assertInstanceOf('Statamic\Data\Pages\PageCollection', $this->page->children());
    }
}
