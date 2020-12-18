<?php

namespace Tests\Feature\GraphQL;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class CollectionTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_queries_a_collection_by_handle()
    {
        Collection::make('blog')->title('Blog Posts')->save();
        Collection::make('events')->title('Events')->save();

        $query = <<<'GQL'
{
    collection(handle: "events") {
        handle
        title
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $query])
            ->assertGqlOk()
            ->assertExactJson(['data' => [
                'collection' => [
                    'handle' => 'events',
                    'title' => 'Events',
                ],
            ]]);
    }

    /** @test */
    public function it_queries_the_structure_and_its_tree()
    {
        config(['app.debug' => true]);

        $collection = Collection::make('pages')->title('Pages')->routes(['en' => '{parent_uri}/{slug}']);
        $structure = (new CollectionStructure)->collection($collection)->maxDepth(3)->expectsRoot(true);
        $collection->structure($structure)->save();

        EntryFactory::collection('pages')->id('home')->slug('home')->data(['title' => 'Home'])->create();
        EntryFactory::collection('pages')->id('about')->slug('about')->data(['title' => 'About'])->create();
        EntryFactory::collection('pages')->id('team')->slug('team')->data(['title' => 'Team'])->create();

        $collection->structure()->in('en')->tree([
            ['entry' => 'home'],
            ['entry' => 'about', 'children' => [
                ['entry' => 'team'],
            ]],
        ])->save();

        $query = <<<'GQL'
{
    collection(handle: "pages") {
        structure {
            max_depth
            expects_root
            tree {
                depth
                page {
                    id
                    title
                    url
                }
                children {
                    depth
                    page {
                        id
                        title
                        url
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
                'collection' => [
                    'structure' => [
                        'max_depth' => 3,
                        'expects_root' => true,
                        'tree' => [
                            [
                                'depth' => 1,
                                'page' => [
                                    'id' => 'home',
                                    'title' => 'Home',
                                    'url' => '/',
                                ],
                                'children' => [],
                            ],
                            [
                                'depth' => 1,
                                'page' => [
                                    'id' => 'about',
                                    'title' => 'About',
                                    'url' => '/about',
                                ],
                                'children' => [
                                    [
                                        'depth' => 2,
                                        'page' => [
                                            'id' => 'team',
                                            'title' => 'Team',
                                            'url' => '/about/team',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]]);
    }
}
