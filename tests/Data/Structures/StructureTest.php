<?php

namespace Tests\Data\Structures;

use Tests\TestCase;
use Statamic\API\Entry;
use Illuminate\Support\Collection;
use Statamic\Data\Structures\Page;
use Statamic\Data\Structures\Pages;
use Statamic\Data\Structures\Structure;
use Statamic\API\Structure as StructureAPI;

class StructureTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app->make('stache')->withoutBooting(function ($stache) {
            $dir = __DIR__.'/../../Stache/__fixtures__';
            $stache->store('collections')->directory($dir . '/content/collections');
            $stache->store('entries')->directory($dir . '/content/collections');
        });
    }

    /** @test */
    function it_gets_and_sets_the_handle()
    {
        $structure = new Structure;
        $this->assertNull($structure->handle());

        $return = $structure->handle('test');

        $this->assertEquals('test', $structure->handle());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    function it_gets_and_sets_the_data()
    {
        $structure = new Structure;
        $this->assertEquals([], $structure->data());

        $return = $structure->data(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $structure->data());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    function it_gets_and_sets_the_title()
    {
        $structure = (new Structure)->handle('test');

        // No title set falls back to uppercased version of the handle
        $this->assertEquals('Test', $structure->title());

        $return = $structure->title('Explicitly set title');

        $this->assertEquals('Explicitly set title', $structure->title());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    function it_gets_and_sets_the_route()
    {
        $structure = new Structure;
        $this->assertNull($structure->route());

        $return = $structure->route('test');

        $this->assertEquals('test', $structure->route());
        $this->assertEquals($structure, $return);
    }

    /** @test */
    function it_saves_the_structure_through_the_api()
    {
        $structure = new Structure;
        $structure->data(['foo' => 'bar']);
        StructureAPI::shouldReceive('save')->with($structure)->once();

        $structure->save();
    }

    /** @test */
    function it_gets_the_parent()
    {
        $structure = $this->structure();

        $parent = $structure->parent();

        $this->assertInstanceOf(Page::class, $parent);
        $this->assertEquals(Entry::find('pages-home'), $parent->entry());
    }

    /** @test */
    function it_gets_the_child_pages_including_the_parent_by_default()
    {
        $pages = $this->structure()->pages();

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(3, $pages->all());
    }

    /** @test */
    function it_gets_the_child_pages_without_the_parent()
    {
        $pages = $this->structure()->withoutParent()->pages();

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(2, $pages->all());
    }

    /** @test */
    function it_gets_the_page_uris()
    {
        $this->assertEquals([
            'pages-home' => '/',
            'pages-about' => '/about',
            'pages-board' => '/about/board',
            'pages-directors' => '/about/board/directors',
            'pages-blog' => '/blog',
        ], $this->structure()->uris()->all());
    }

    /** @test */
    function it_can_exclude_the_parent()
    {
        $this->assertEquals([
            'pages-about' => '/about',
            'pages-board' => '/about/board',
            'pages-directors' => '/about/board/directors',
            'pages-blog' => '/blog',
        ], $this->structure()->withoutParent()->uris()->all());
    }

    /** @test */
    function it_gets_a_page_by_key()
    {
        $page = $this->structure()->page('pages-directors');

        $this->assertEquals('Directors', $page->get('title'));
    }

    protected function structure()
    {
        return (new Structure)->data([
            'route' => '{parent_uri}/{slug}',
            'root' => 'pages-home',
            'tree' => [
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
            ]
        ]);
    }
}
