<?php

namespace Tests\Data;

use Tests\TestCase;
use Statamic\API\Page;
use Statamic\API\Site;
use Statamic\API\Config;

class ContentTest extends TestCase
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

    public function testGetsFolder()
    {
        $this->assertEquals('about', $this->page->folder());
    }

    public function testChangesStatus()
    {
        $this->page->unpublish();
        $this->assertFalse($this->page->published());
        $this->page->publish();
        $this->assertTrue($this->page->published());
    }

    public function test_that_a_url_can_get_retrieved()
    {
        Site::setConfig('sites.en.url', 'http://foo.com/');

        $this->assertEquals('/about', $this->page->url());
        $this->assertEquals('http://foo.com/about', $this->page->absoluteUrl());
    }

    public function test_that_content_can_have_an_order()
    {
        $this->assertNull($this->page->order());

        $this->page->order(1);

        $this->assertEquals(1, $this->page->order());
    }

    public function test_that_content_can_have_a_published_status()
    {
        $this->assertTrue($this->page->published());

        $this->page->unpublish();
        $this->assertFalse($this->page->published());

        $this->page->publish();
        $this->assertTrue($this->page->published());

        $this->page->published(false);
        $this->assertFalse($this->page->published());

        $this->page->published(true);
        $this->assertTrue($this->page->published());
    }
}
