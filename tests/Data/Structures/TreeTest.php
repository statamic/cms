<?php

namespace Tests\Data\Structures;

use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Structures\Nav;
use Statamic\Structures\Page;
use Statamic\Structures\Pages;
use Statamic\Structures\Structure;
use Statamic\Structures\Tree;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;
use Tests\UnlinksPaths;

class TreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UnlinksPaths;

    public function setUp(): void
    {
        parent::setUp();

        $stache = $this->app->make('stache');
        $dir = __DIR__.'/../../Stache/__fixtures__';
        $stache->store('collections')->directory($dir.'/content/collections');
        $stache->store('entries')->directory($dir.'/content/collections');
    }

    /** @test */
    public function it_gets_the_route_from_the_structure()
    {
        $structure = $this->mock(Structure::class);
        $structure->shouldReceive('route')->with('the-locale')->once()->andReturn('/the-route/{slug}');

        $tree = (new Tree)
            ->locale('the-locale')
            ->structure($structure);

        $this->assertEquals('/the-route/{slug}', $tree->route());
    }

    /** @test */
    public function it_gets_the_edit_url()
    {
        $structure = $this->mock(Structure::class);
        $structure->shouldReceive('editUrl')->withNoArgs()->once()->andReturn('/edit-url');

        $tree = (new Tree)->structure($structure);

        $this->assertEquals('/edit-url', $tree->editUrl());
    }

    /** @test */
    public function it_gets_the_delete_url()
    {
        $structure = $this->mock(Structure::class);
        $structure->shouldReceive('deleteUrl')->withNoArgs()->once()->andReturn('/delete-url');

        $tree = (new Tree)->structure($structure);

        $this->assertEquals('/delete-url', $tree->deleteUrl());
    }

    /** @test */
    public function it_gets_the_show_url_from_the_structure()
    {
        Site::shouldReceive('hasMultiple')->once()->andReturnFalse();
        $structure = $this->mock(Structure::class);
        $structure->shouldReceive('showUrl')->with([])->once()->andReturn('/show-url');

        $tree = (new Tree)
            ->locale('the-locale')
            ->structure($structure);

        $this->assertEquals('/show-url', $tree->showUrl());
    }

    /** @test */
    public function it_gets_the_show_url_with_the_site_query_param_when_there_are_multiple_sites()
    {
        Site::shouldReceive('hasMultiple')->once()->andReturnTrue();
        $structure = $this->mock(Structure::class);
        $structure->shouldReceive('showUrl')->with(['site' => 'the-locale'])->once()->andReturn('/show-url');

        $tree = (new Tree)
            ->locale('the-locale')
            ->structure($structure);

        $this->assertEquals('/show-url', $tree->showUrl());
    }

    /** @test */
    public function it_gets_the_parent()
    {
        $tree = $this->tree();

        $parent = $tree->parent();

        $this->assertInstanceOf(Page::class, $parent);
        $this->assertEquals(Entry::find('pages-home'), $parent->entry());
    }

    /** @test */
    public function it_gets_the_root()
    {
        $tree = $this->tree();
        $tree->structure()->expectsRoot(true);
        $tree->tree([['entry' => 'pages-home']]);

        $this->assertEquals('pages-home', $tree->root());
    }

    /** @test */
    public function a_tree_not_expecting_a_root_will_have_no_root()
    {
        $tree = $this->tree();
        $tree->structure()->expectsRoot(false);
        $tree->tree([['entry' => 'pages-home']]);

        $this->assertNull($tree->root());
    }

    /** @test */
    public function a_tree_expecting_a_root_but_with_no_branches_has_no_root()
    {
        $tree = $this->tree();
        $tree->structure()->expectsRoot(true);
        $tree->tree([]);

        $this->assertNull($tree->root());
    }

    /** @test */
    public function it_gets_the_child_pages_including_the_root()
    {
        $pages = $this->tree()->pages();

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(3, $pages->all());
    }

    /** @test */
    public function it_gets_a_page_by_key()
    {
        $page = $this->tree()->page('pages-directors');

        $this->assertEquals('Directors', $page->title());
    }

    /** @test */
    public function it_appends_an_entry()
    {
        $tree = $this->tree();

        $tree->append(Entry::make()->id('appended-page'));

        $this->assertEquals([
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                        'children' => [
                            [
                                'entry' => 'pages-directors',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'entry' => 'pages-blog',
            ],
            [
                'entry' => 'appended-page',
            ],
        ], $tree->tree());
    }

    /** @test */
    public function it_appends_an_entry_to_another_page()
    {
        $tree = $this->tree();

        $tree->appendTo('pages-board', Entry::make()->id('appended-page'));

        $this->assertEquals([
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                        'children' => [
                            [
                                'entry' => 'pages-directors',
                            ],
                            [
                                'entry' => 'appended-page',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'entry' => 'pages-blog',
            ],
        ], $tree->tree());
    }

    /** @test */
    public function it_moves_an_entry_to_another_page()
    {
        $tree = $this->tree();

        // Add [foo=>bar] to the directors page, just so we can test the whole array gets moved.
        $treeContent = $tree->tree();
        $treeContent[1]['children'][0]['children'][0]['foo'] = 'bar';
        $tree->tree($treeContent);

        $tree->move('pages-directors', 'pages-about');

        $this->assertEquals([
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                    ],
                    [
                        'entry' => 'pages-directors',
                        'foo' => 'bar',
                    ],
                ],
            ],
            [
                'entry' => 'pages-blog',
            ],
        ], $tree->tree());
    }

    /** @test */
    public function it_doesnt_get_moved_if_its_already_in_the_target()
    {
        $tree = $this->tree()->tree($arr = [
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                    ],
                    [
                        'entry' => 'pages-directors',
                    ],
                ],
            ],
            [
                'entry' => 'pages-blog',
            ],
        ]);

        $tree->move('pages-board', 'pages-about');

        $this->assertEquals($arr, $tree->tree());
    }

    /**
     * @test
     * @see https://github.com/statamic/cms/issues/3148
     */
    public function it_doesnt_get_moved_to_root_if_its_already_there_and_the_target_is_null()
    {
        $tree = $this->tree()->tree($arr = [
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                    ],
                    [
                        'entry' => 'pages-directors',
                    ],
                ],
            ],
            [
                'entry' => 'pages-blog',
            ],
        ]);

        $tree->move('pages-about', null);

        $this->assertEquals($arr, $tree->tree());
    }

    /**
     * @test
     * @see https://github.com/statamic/cms/issues/1548
     **/
    public function it_can_move_the_root()
    {
        $tree = $this->tree()->tree([
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-blog',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                        'children' => [
                            [
                                'entry' => 'pages-directors',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $tree->move('pages-home', 'pages-board');

        $this->assertEquals([
            [
                'entry' => 'pages-blog',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                        'children' => [
                            [
                                'entry' => 'pages-directors',
                            ],
                            [
                                'entry' => 'pages-home',
                            ],
                        ],
                    ],
                ],
            ],
        ], $tree->tree());
    }

    /** @test */
    public function it_fixes_indexes_when_moving()
    {
        $tree = $this->tree()->tree([
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-blog',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                    ],
                ],
            ],
        ]);

        $tree->move('pages-blog', 'pages-about');

        // If the indexes hadn't been fixed, we'd have an array starting with 1.
        $this->assertEquals([
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                    ],
                    [
                        'entry' => 'pages-blog',
                    ],
                ],
            ],
        ], $tree->tree());
    }

    /** @test */
    public function the_structure_validates_the_tree_when_getting_it_the_first_time()
    {
        $structure = $this->mock(Structure::class);
        $structure->shouldReceive('handle')->andReturn('test');

        $firstContents = ['first' => 'time'];
        $secondContents = ['second' => 'time'];

        $structure->shouldReceive('validateTree')->with($firstContents, 'the-locale')->once()->andReturn($firstContents);
        $structure->shouldReceive('validateTree')->with($secondContents, 'the-locale')->once()->andReturn($secondContents);

        $tree = (new Tree)->structure($structure)->locale('the-locale');

        // Calling tree multiple times doesn't re-validate
        $tree->tree($firstContents);
        $tree->tree();
        $tree->tree();

        // Re-setting the tree exactly the same also won't re-validate
        $tree->tree($firstContents);
        $tree->tree();

        // Using different contents will re-validate.
        $tree->tree($secondContents);
        $tree->tree();
        $tree->tree($secondContents);
        $tree->tree();
        $tree->tree();
    }

    /** @test */
    public function it_cannot_move_into_root_if_structure_expects_root()
    {
        $this->expectExceptionMessage('Root page cannot have children');

        $tree = $this->tree()->tree([
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                    ],
                    [
                        'entry' => 'pages-directors',
                    ],
                ],
            ],
            [
                'entry' => 'pages-blog',
            ],
        ]);

        $tree->move('pages-board', 'pages-home');
    }

    /** @test */
    public function it_can_move_into_root_if_structure_does_not_expect_root()
    {
        $tree = $this->tree();
        $tree->structure()->expectsRoot(false);

        $tree->tree([
            [
                'entry' => 'pages-home',
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-board',
                    ],
                    [
                        'entry' => 'pages-directors',
                    ],
                ],
            ],
            [
                'entry' => 'pages-blog',
            ],
        ]);

        $tree->move('pages-board', 'pages-home');

        $this->assertEquals([
            [
                'entry' => 'pages-home',
                'children' => [
                    [
                        'entry' => 'pages-board',
                    ],
                ],
            ],
            [
                'entry' => 'pages-about',
                'children' => [
                    [
                        'entry' => 'pages-directors',
                    ],
                ],
            ],
            [
                'entry' => 'pages-blog',
            ],
        ], $tree->tree());
    }

    protected function tree()
    {
        return (new Tree)
            ->locale('en')
            ->structure((new Nav)->expectsRoot(true))
            ->tree([
                [
                    'entry' => 'pages-home',
                ],
                [
                    'entry' => 'pages-about',
                    'children' => [
                        [
                            'entry' => 'pages-board',
                            'children' => [
                                [
                                    'entry' => 'pages-directors',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'entry' => 'pages-blog',
                ],
            ]);
    }
}
