<?php

namespace Tests\Data\Structures;

use Tests\TestCase;
use Statamic\API\Entry;
use Illuminate\Support\Collection;
use Statamic\Data\Structures\Page;
use Statamic\Data\Structures\Tree;
use Statamic\Data\Structures\Pages;
use Statamic\Data\Structures\Structure;
use Statamic\API\Structure as StructureAPI;

class TreeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app->make('stache')->withoutBooting(function ($stache) {
            $dir = __DIR__.'/../../Stache/__fixtures__';
            $stache->store('collections')->directory($dir . '/content/collections');
            $stache->store('entries')->directory($dir . '/content/collections');
        });
    }

    /** @test */
    function it_gets_and_sets_the_route()
    {
        $structure = new Tree;
        $this->assertNull($structure->route());

        $return = $structure->route('test');

        $this->assertEquals('test', $structure->route());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    function it_gets_the_parent()
    {
        $structure = $this->tree();

        $parent = $structure->parent();

        $this->assertInstanceOf(Page::class, $parent);
        $this->assertEquals(Entry::find('pages-home'), $parent->entry());
    }

    /** @test */
    function it_gets_the_child_pages_including_the_parent_by_default()
    {
        $pages = $this->tree()->pages();

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(3, $pages->all());
    }

    /** @test */
    function it_gets_the_child_pages_without_the_parent()
    {
        $pages = $this->tree()->withoutParent()->pages();

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(2, $pages->all());
    }

    /** @test */
    function it_gets_a_page_by_key()
    {
        $page = $this->tree()->page('pages-directors');

        $this->assertEquals('Directors', $page->title());
    }

    protected function tree()
    {
        return (new Tree)
            ->route('{parent_uri}/{slug}')
            ->root('pages-home')
            ->tree([
                [
                    'entry' => 'pages-about',
                    'children' => [
                        [
                            'entry' => 'pages-board',
                            'children' => [
                                [
                                    'entry' => 'pages-directors'
                                ]
                            ]
                        ]
                    ],
                ],
                [
                    'entry' => 'pages-blog'
                ],
            ]);
    }
}
