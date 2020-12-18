<?php

namespace Tests\Feature\GraphQL;

use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class NavTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_a_nav_by_handle()
    {
        Nav::make('links')->title('Links')->maxDepth(1)->expectsRoot(false)->tap(function ($nav) {
            $nav->addTree($nav->makeTree('en'));
            $nav->save();
        });
        $this->createFooterNav();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        handle
        title
        max_depth
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

    private function createFooterNav()
    {
        Nav::make('footer')->title('Footer')->maxDepth(3)->expectsRoot(false)->tap(function ($nav) {
            $nav->addTree($nav->makeTree('en')->tree([
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
                ], ],
                [
                    'url' => '/two',
                    'title' => 'Two',
                ],
            ]));
            $nav->save();
        });
    }
}
