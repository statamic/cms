<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\Fields\BlueprintRepository;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class NavTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use CreatesQueryableTestEntries;
    use EnablesQueries;

    protected $enabledQueries = ['navs'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    /**
     * @test
     * @environment-setup disableQueries
     **/
    public function query_only_works_if_enabled()
    {
        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{nav}'])
            ->assertSee('Cannot query field \"nav\" on type \"Query\"', false);
    }

    /** @test */
    public function it_queries_a_nav_by_handle()
    {
        Nav::make('links')->title('Links')->maxDepth(1)->expectsRoot(false)->tap(function ($nav) {
            $nav->makeTree('en')->save();
        })->save();
        $this->createFooterNav();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        handle
        title
        max_depth
        expects_root
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'nav' => [
                    'handle' => 'footer',
                    'title' => 'Footer',
                    'max_depth' => 3,
                    'expects_root' => false,
                ],
            ]]);
    }

    /** @test */
    public function it_queries_the_tree_inside_a_nav()
    {
        $this->createFooterNav();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        tree {
            depth
            page {
                url
            }
            children {
                depth
                page {
                    url
                }
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'nav' => [
                    'tree' => [
                        [
                            'depth' => 1,
                            'page' => ['url' => '/one'],
                            'children' => [
                                [
                                    'depth' => 2,
                                    'page' => ['url' => '/one/nested'],
                                ],
                            ],
                        ],
                        [
                            'depth' => 1,
                            'page' => ['url' => '/two'],
                            'children' => [],
                        ],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_queries_the_tree_inside_a_nav_using_fragments_for_pseudo_recursion()
    {
        // Courtesy of https://hashinteractive.com/blog/graphql-recursive-query-with-fragments/

        $this->createFooterNav();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        tree {
            ...Children
            ...RecursiveChildren
        }
    }
}

fragment Children on TreeBranch {
    depth
    page {
        url
    }
}

fragment RecursiveChildren on TreeBranch {
    children {
        ...Children
        children {
            ...Children
            children {
                ...Children
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'nav' => [
                    'tree' => [
                        [
                            'depth' => 1,
                            'page' => ['url' => '/one'],
                            'children' => [
                                [
                                    'depth' => 2,
                                    'page' => ['url' => '/one/nested'],
                                    'children' => [
                                        [
                                            'depth' => 3,
                                            'page' => ['url' => '/one/nested/double-nested'],
                                            'children' => [
                                                [
                                                    'depth' => 4,
                                                    'page' => ['url' => '/one/nested/double-nested/triple-nested'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'depth' => 1,
                            'page' => ['url' => '/two'],
                            'children' => [],
                        ],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_queries_the_tree_inside_a_nav_with_entries()
    {
        $this->createEntries();

        Nav::make('footer')->title('Footer')->maxDepth(3)->expectsRoot(false)->tap(function ($nav) {
            $nav->makeTree('en', [
                [
                    'entry' => '1',
                    'children' => [
                        [
                            'entry' => '2',
                        ],
                    ],
                ],
            ])->save();
        })->save();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        tree {
            depth
            ...Page

            children {
                depth
                ...Page
            }
        }
    }
}

fragment Page on TreeBranch {
    page {
        id
        title
        slug
        ... on EntryPage_Blog_Article {
            intro
        }
        ... on EntryPage_Blog_ArtDirected {
            hero_image
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'nav' => [
                    'tree' => [
                        [
                            'depth' => 1,
                            'page' => [
                                'id' => '1',
                                'title' => 'Standard Blog Post',
                                'slug' => 'standard-blog-post',
                                'intro' => 'The intro',
                            ],
                            'children' => [
                                [
                                    'depth' => 2,
                                    'page' => [
                                        'id' => '2',
                                        'title' => 'Art Directed Blog Post',
                                        'slug' => 'art-directed-blog-post',
                                        'hero_image' => 'hero.jpg',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_queries_the_tree_inside_a_nav_in_a_specific_site()
    {
        $this->createFooterNav();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        tree(site: "fr") {
            depth
            page {
                url
            }
            children {
                depth
                page {
                    url
                }
            }
        }
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'nav' => [
                    'tree' => [
                        [
                            'depth' => 1,
                            'page' => ['url' => '/fr-one'],
                            'children' => [
                                [
                                    'depth' => 2,
                                    'page' => ['url' => '/fr-one/fr-nested'],
                                ],
                            ],
                        ],
                        [
                            'depth' => 1,
                            'page' => ['url' => '/fr-two'],
                            'children' => [],
                        ],
                    ],
                ],
            ]]);
    }

    private function createFooterNav()
    {
        Nav::make('footer')->title('Footer')->maxDepth(3)->expectsRoot(false)->tap(function ($nav) {
            $nav->makeTree('en', [
                [
                    'url' => '/one',
                    'title' => 'One',
                    'children' => [
                        [
                            'url' => '/one/nested',
                            'title' => 'Nested',
                            'children' => [
                                [
                                    'url' => '/one/nested/double-nested',
                                    'title' => 'Double Nested',
                                    'children' => [
                                        [
                                            'url' => '/one/nested/double-nested/triple-nested',
                                            'title' => 'Triple Nested',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'url' => '/two',
                    'title' => 'Two',
                ],
            ])->save();
            $nav->makeTree('fr', [
                [
                    'url' => '/fr-one',
                    'title' => 'Fr One',
                    'children' => [
                        [
                            'url' => '/fr-one/fr-nested',
                            'title' => 'Fr Nested',
                            'children' => [
                                [
                                    'url' => '/fr-one/fr-nested/fr-double-nested',
                                    'title' => 'Fr Double Nested',
                                    'children' => [
                                        [
                                            'url' => '/fr-one/fr-nested/fr-double-nested/fr-triple-nested',
                                            'title' => 'Fr Triple Nested',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'url' => '/fr-two',
                    'title' => 'Fr Two',
                ],
            ])->save();
        })->save();
    }
}
