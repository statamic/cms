<?php

namespace Tests\Feature\GraphQL;

use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class PageTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    private function createData()
    {
        $collection = Collection::make('pages')->title('Pages')->routes(['en' => '{parent_uri}/{slug}']);
        $structure = (new CollectionStructure)->collection($collection)->maxDepth(3)->expectsRoot(true);
        $collection->structure($structure)->save();

        EntryFactory::collection('pages')->id('home')->slug('home')->create();
        EntryFactory::collection('pages')->id('about')->slug('about')->create();
        EntryFactory::collection('pages')->id('team')->slug('team')->create();

        $collection->structure()->in('en')->tree([
            ['entry' => 'home'],
            ['entry' => 'about', 'children' => [
                ['entry' => 'team'],
            ]],
        ])->save();
    }

    private function structureQuery()
    {
        return <<<'GQL'
{
    collection(handle: "pages") {
        structure {
            tree {
                depth
                page {
                    id
                    custom
                }
                children {
                    depth
                    page {
                        id
                        custom
                    }
                }
            }
        }
    }
}
GQL;
    }

    private function expectedStructureQueryResponse()
    {
        return ['data' => [
            'collection' => [
                'structure' => [
                    'tree' => [
                        [
                            'depth' => 1,
                            'page' => [
                                'id' => 'home',
                                'custom' => 'custom home',
                            ],
                            'children' => [],
                        ],
                        [
                            'depth' => 1,
                            'page' => [
                                'id' => 'about',
                                'custom' => 'custom about',
                            ],
                            'children' => [
                                [
                                    'depth' => 2,
                                    'page' => [
                                        'id' => 'team',
                                        'custom' => 'custom team',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]];
    }

    /** @test */
    public function adding_a_field_to_entry_interface_will_appear_within_pages()
    {
        GraphQL::addField('EntryInterface', 'custom', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($page) {
                    return 'custom '.$page->id();
                },
            ];
        });

        $this->createData();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $this->structureQuery()])
            ->assertGqlOk()
            ->assertExactJson($this->expectedStructureQueryResponse());
    }

    /** @test */
    public function adding_a_field_to_page_interface_will_appear_within_pages_but_not_entries()
    {
        GraphQL::addField('PageInterface', 'custom', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($page) {
                    return 'custom '.$page->id();
                },
            ];
        });

        $this->createData();

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $this->structureQuery()])
            ->assertGqlOk()
            ->assertExactJson($this->expectedStructureQueryResponse());

        $entryQuery = <<<'GQL'
{
    entry(id: "home") {
        id
        custom
    }
}
GQL;

        $this
            ->withoutExceptionHandling()
            ->post('/graphql', ['query' => $entryQuery])
            ->assertJson(['errors' => [[
                'message' => 'Cannot query field "custom" on type "EntryInterface".',
            ]]]);
    }

    /** @test */
    public function it_can_add_custom_fields_to_an_implementation()
    {
        GraphQL::addField('EntryPage_Pages_Pages', 'custom', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($page) {
                    return 'custom '.$page->id();
                },
            ];
        });

        $this->createData();

        $query = <<<'GQL'
{
    collection(handle: "pages") {
        structure {
            tree {
                depth
                page {
                    id
                    ... on EntryPage_Pages_Pages {
                        custom
                    }
                }
                children {
                    depth
                    page {
                        id
                        ... on EntryPage_Pages_Pages {
                            custom
                        }
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
            ->assertExactJson($this->expectedStructureQueryResponse());
    }
}
