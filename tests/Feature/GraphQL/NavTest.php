<?php

namespace Tests\Feature\GraphQL;

use Facades\Statamic\API\ResourceAuthorizer;
use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Nav;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class NavTest extends TestCase
{
    use CreatesQueryableTestEntries;
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['navs'];

    #[Test]
    public function query_only_works_if_enabled()
    {
        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'navs')->andReturnFalse()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'navs')->never();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => '{nav}'])
            ->assertSee('Cannot query field \"nav\" on type \"Query\"', false);
    }

    #[Test]
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

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'navs')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'navs')->andReturn(Nav::all()->map->handle()->all())->once();
        ResourceAuthorizer::makePartial();

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

    #[Test]
    public function it_cannot_query_against_non_allowed_sub_resource()
    {
        $this->createFooterNav();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        handle
        title
    }
}
GQL;

        ResourceAuthorizer::shouldReceive('isAllowed')->with('graphql', 'navs')->andReturnTrue()->once();
        ResourceAuthorizer::shouldReceive('allowedSubResources')->with('graphql', 'navs')->andReturn([])->once();
        ResourceAuthorizer::makePartial();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertJson([
                'errors' => [[
                    'message' => 'validation',
                    'extensions' => [
                        'validation' => [
                            'handle' => ['Forbidden: footer'],
                        ],
                    ],
                ]],
                'data' => [
                    'nav' => null,
                ],
            ]);
    }

    #[Test]
    public function it_queries_the_tree_inside_a_nav()
    {
        $this->createFooterNav();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        tree {
            depth
            page {
                id
                title
                url
                ... on NavPage_Footer {
                    foo
                }
            }
            children {
                depth
                page {
                    id
                    title
                    url
                    ... on NavPage_Footer {
                        foo
                    }
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
                            'page' => [
                                'id' => 'id-one',
                                'title' => 'One',
                                'url' => '/one',
                                'foo' => 'bar',
                            ],
                            'children' => [
                                [
                                    'depth' => 2,
                                    'page' => [
                                        'id' => 'id-one-nested',
                                        'title' => 'Nested',
                                        'url' => '/one/nested',
                                        'foo' => 'baz',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'depth' => 1,
                            'page' => [
                                'id' => 'id-two',
                                'title' => 'Two',
                                'url' => '/two',
                                'foo' => null,
                            ],
                            'children' => [],
                        ],
                        [
                            'depth' => 1,
                            'page' => [
                                'id' => 'id-just-url',
                                'title' => null,
                                'url' => '/just-url',
                                'foo' => null,
                            ],
                            'children' => [],
                        ],
                        [
                            'depth' => 1,
                            'page' => [
                                'id' => 'id-entry',
                                'title' => 'Entry Title',
                                'url' => '/blog/the-entry',
                                'foo' => 'overridden foo',
                            ],
                            'children' => [],
                        ],
                    ],
                ],
            ]]);
    }

    #[Test]
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

fragment Children on NavTreeBranch {
    depth
    page {
        url
    }
}

fragment RecursiveChildren on NavTreeBranch {
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
                        [
                            'depth' => 1,
                            'page' => ['url' => '/just-url'],
                            'children' => [],
                        ],
                        [
                            'depth' => 1,
                            'page' => ['url' => '/blog/the-entry'],
                            'children' => [],
                        ],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_queries_the_tree_inside_a_nav_with_entries()
    {
        $this->createEntries();

        BlueprintRepository::partialMock();
        $blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);
        BlueprintRepository::shouldReceive('find')->with('navigation.footer')->andReturn($blueprint);

        Nav::make('footer')->title('Footer')->collections(['blog'])->maxDepth(3)->expectsRoot(false)->tap(function ($nav) {
            $nav->makeTree('en', [
                [
                    'id' => 'id-one',
                    'entry' => '1',
                    'data' => ['foo' => 'bar'],
                    'children' => [
                        [
                            'id' => 'id-two',
                            'entry' => '2',
                            'data' => ['foo' => 'baz'],
                        ],
                    ],
                ],
                [
                    'id' => 'id-not-entry',
                    'url' => '/not-an-entry',
                    'title' => 'Not an entry',
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

fragment Page on NavTreeBranch {
    page {
        id
        entry_id
        title
        ... on EntryInterface {
            slug
        }
        ... on NavEntryPage_Footer_Blog_Article {
            foo
            intro
            edit_url
        }
        ... on NavEntryPage_Footer_Blog_ArtDirected {
            foo
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
                                'id' => 'id-one',
                                'entry_id' => '1',
                                'title' => 'Standard Blog Post',
                                'slug' => 'standard-blog-post',
                                'intro' => 'The intro',
                                'foo' => 'bar',
                                'edit_url' => 'http://localhost/cp/collections/blog/entries/1',
                            ],
                            'children' => [
                                [
                                    'depth' => 2,
                                    'page' => [
                                        'id' => 'id-two',
                                        'entry_id' => '2',
                                        'title' => 'Art Directed Blog Post',
                                        'slug' => 'art-directed-blog-post',
                                        'hero_image' => 'hero.jpg',
                                        'foo' => 'baz',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'depth' => 1,
                            'page' => [
                                'id' => 'id-not-entry',
                                'entry_id' => null,
                                'title' => 'Not an entry',
                            ],
                            'children' => [],
                        ],
                    ],
                ],
            ]]);
    }

    #[Test]
    public function it_queries_the_tree_inside_a_nav_in_a_specific_site()
    {
        config(['app.debug' => true]);
        $this->createFooterNav();

        $query = <<<'GQL'
{
    nav(handle: "footer") {
        tree(site: "fr") {
            depth
            page {
                url
                ... on NavPage_Footer {
                    foo
                }
            }
            children {
                depth
                page {
                    url
                    ... on NavPage_Footer {
                        foo
                    }
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
                            'page' => ['url' => '/fr-one', 'foo' => 'le-bar'],
                            'children' => [
                                [
                                    'depth' => 2,
                                    'page' => ['url' => '/fr-one/fr-nested', 'foo' => null],
                                ],
                            ],
                        ],
                        [
                            'depth' => 1,
                            'page' => ['url' => '/fr-two', 'foo' => null],
                            'children' => [],
                        ],
                    ],
                ],
            ]]);
    }

    private function createFooterNav()
    {
        Collection::make('blog')->routes('/blog/{slug}')->save();
        EntryFactory::id('1')->slug('the-entry')->collection('blog')->data(['title' => 'Entry Title', 'foo' => 'foo in entry'])->create();

        BlueprintRepository::partialMock();
        $blueprint = Blueprint::makeFromFields(['foo' => ['type' => 'text']]);
        BlueprintRepository::shouldReceive('find')->with('navigation.footer')->andReturn($blueprint);

        Nav::make('footer')->title('Footer')->maxDepth(3)->expectsRoot(false)->collections(['blog'])->tap(function ($nav) {
            $nav->makeTree('en', [
                [
                    'id' => 'id-one',
                    'url' => '/one',
                    'title' => 'One',
                    'data' => ['foo' => 'bar'],
                    'children' => [
                        [
                            'id' => 'id-one-nested',
                            'url' => '/one/nested',
                            'title' => 'Nested',
                            'data' => ['foo' => 'baz'],
                            'children' => [
                                [
                                    'id' => 'id-one-nested-doublenested',
                                    'url' => '/one/nested/double-nested',
                                    'title' => 'Double Nested',
                                    'children' => [
                                        [
                                            'id' => 'id-one-nested-doublenested-tripenested',
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
                    'id' => 'id-two',
                    'url' => '/two',
                    'title' => 'Two',
                ],
                [
                    'id' => 'id-just-url',
                    'url' => '/just-url',
                ],
                [
                    'id' => 'id-entry',
                    'entry' => '1',
                    'data' => ['foo' => 'overridden foo'],
                ],
            ])->save();
            $nav->makeTree('fr', [
                [
                    'id' => 'id-fr-one',
                    'url' => '/fr-one',
                    'title' => 'Fr One',
                    'data' => ['foo' => 'le-bar'],
                    'children' => [
                        [
                            'id' => 'id-fr-one-nested',
                            'url' => '/fr-one/fr-nested',
                            'title' => 'Fr Nested',
                            'children' => [
                                [
                                    'id' => 'id-fr-one-nested-doublenested',
                                    'url' => '/fr-one/fr-nested/fr-double-nested',
                                    'title' => 'Fr Double Nested',
                                    'children' => [
                                        [
                                            'id' => 'id-fr-one-nested-doublenested-tripenested',
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
                    'id' => 'id-fr-two',
                    'url' => '/fr-two',
                    'title' => 'Fr Two',
                ],
            ])->save();
        })->save();
    }
}
