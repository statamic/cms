<?php

namespace Tests\Data\Structures;

use Tests\TestCase;
use Tests\UnlinksPaths;
use Statamic\Facades\Entry;
use Statamic\Facades\Collection;
use Statamic\Structures\Page;
use Statamic\Structures\Tree;
use Statamic\Structures\Pages;
use Statamic\Structures\Structure;
use Tests\PreventSavingStacheItemsToDisk;
use Statamic\Facades\Structure as StructureAPI;

class TreeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use UnlinksPaths;

    public function setUp(): void
    {
        parent::setUp();

        $stache = $this->app->make('stache');
        $dir = __DIR__.'/../../Stache/__fixtures__';
        $stache->store('collections')->directory($dir . '/content/collections');
        $stache->store('entries')->directory($dir . '/content/collections');
    }

    /** @test */
    function it_gets_the_route_from_the_collection()
    {
        $collection = tap(Collection::make('test-collection')->route('the-uri/{slug}'))->save();

        $this->unlinkAfter($collection->path());

        $structure = (new Structure)->handle('test-structure')->collection($collection);
        $tree = (new Tree)->structure($structure);

        $this->assertEquals('the-uri/{slug}', $tree->route());
    }

    /** @test */
    function a_structure_without_a_collection_has_no_route()
    {
        $structure = (new Structure)->handle('test-structure');
        $tree = (new Tree)->structure($structure);

        $this->assertNull($tree->route());
    }

    /** @test */
    function it_gets_the_parent()
    {
        $tree = $this->tree();

        $parent = $tree->parent();

        $this->assertInstanceOf(Page::class, $parent);
        $this->assertEquals(Entry::find('pages-home'), $parent->entry());
    }

    /** @test */
    function it_gets_the_child_pages_including_the_root()
    {
        $pages = $this->tree()->pages();

        $this->assertInstanceOf(Pages::class, $pages);
        $this->assertCount(3, $pages->all());
    }

    /** @test */
    function it_gets_a_page_by_key()
    {
        $page = $this->tree()->page('pages-directors');

        $this->assertEquals('Directors', $page->title());
    }

    /** @test */
    function it_appends_an_entry()
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
                                'entry' => 'pages-directors'
                            ]
                        ]
                    ]
                ],
            ],
            [
                'entry' => 'pages-blog'
            ],
            [
                'entry' => 'appended-page'
            ],
        ], $tree->tree());
    }

    /** @test */
    function it_appends_an_entry_to_another_page()
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
                                'entry' => 'pages-directors'
                            ],
                            [
                                'entry' => 'appended-page'
                            ],
                        ]
                    ]
                ],
            ],
            [
                'entry' => 'pages-blog',
            ]
        ], $tree->tree());
    }

    /** @test */
    function it_moves_an_entry_to_another_page()
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
                    ]
                ],
            ],
            [
                'entry' => 'pages-blog',
            ]
        ], $tree->tree());
    }

    /** @test */
    function it_doesnt_get_moved_if_its_already_in_the_target()
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
                    ]
                ],
            ],
            [
                'entry' => 'pages-blog',
            ]
        ]);

        $tree->move('pages-board', 'pages-about');

        $this->assertEquals($arr, $tree->tree());
    }

    /** @test */
    function it_fixes_indexes_when_moving()
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
                    ]
                ],
            ]
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
                    ]
                ],
            ]
        ], $tree->tree());
    }

    /** @test */
    function the_root_must_be_an_entry_when_expecting_root()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Root page must be an entry');

        (new Tree)
            ->structure((new Structure)->expectsRoot(true))
            ->tree([
                [
                    'title' => 'Not an entry',
                    'url' => '/test',
                ]
            ]);
    }

    /** @test **/
    function the_root_doesnt_need_to_be_an_entry_when_not_expecting_root()
    {
        $tree = (new Tree)
            ->structure((new Structure)->expectsRoot(false))
            ->tree($contents = [
                [
                    'title' => 'Not an entry',
                    'url' => '/test',
                ]
            ]);

        $this->assertSame($contents, $tree->tree());
    }

    /** @test */
    function the_root_cannot_have_children_when_expecting_root()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Root page cannot have children');

        (new Tree)
            ->structure((new Structure)->expectsRoot(true))
            ->tree([
                [
                    'entry' => '123',
                    'children' => [
                        [
                            'entry' => '456'
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    function the_root_can_have_children_when_not_expecting_root()
    {
        $tree = (new Tree)
            ->structure((new Structure)->expectsRoot(false))
            ->tree($contents = [
                [
                    'entry' => '123',
                    'children' => [
                        [
                            'entry' => '456'
                        ]
                    ]
                ]
            ]);

        $this->assertSame($contents, $tree->tree());
    }

    protected function tree()
    {
        return (new Tree)
            ->structure((new Structure)->expectsRoot(true))
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
