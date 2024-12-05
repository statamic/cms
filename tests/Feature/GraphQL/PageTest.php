<?php

namespace Tests\Feature\GraphQL;

use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Nav;
use Statamic\Structures\CollectionStructure;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class PageTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['navs'];

    private function createData()
    {
        $collection = Collection::make('pages')->title('Pages');
        $structure = (new CollectionStructure)->maxDepth(3)->expectsRoot(true);
        $collection->structure($structure)->save();

        EntryFactory::collection('pages')->id('home')->slug('home')->create();
        EntryFactory::collection('pages')->id('about')->slug('about')->create();
        EntryFactory::collection('pages')->id('team')->slug('team')->create();

        $nav = tap(Nav::make('links')->collections(['pages']))->save();
        $nav->makeTree('en', [
            ['id' => 'a', 'entry' => 'home'],
            ['id' => 'b', 'entry' => 'about', 'children' => [
                ['id' => 'c', 'entry' => 'team'],
            ]],
        ])->save();
    }

    private function structureQuery()
    {
        return <<<'GQL'
{
    nav(handle: "links") {
            tree {
                depth
                page {
                    entry_id
                    custom
                }
                children {
                    depth
                    page {
                        entry_id
                        custom
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
            'nav' => [
                'tree' => [
                    [
                        'depth' => 1,
                        'page' => [
                            'entry_id' => 'home',
                            'custom' => 'custom home',
                        ],
                        'children' => [],
                    ],
                    [
                        'depth' => 1,
                        'page' => [
                            'entry_id' => 'about',
                            'custom' => 'custom about',
                        ],
                        'children' => [
                            [
                                'depth' => 2,
                                'page' => [
                                    'entry_id' => 'team',
                                    'custom' => 'custom team',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]];
    }

    #[Test]
    public function custom_fields_can_be_added_to_interface()
    {
        GraphQL::addField('PageInterface', 'custom', function () {
            return [
                'type' => GraphQL::string(),
                'resolve' => function ($page) {
                    return 'custom '.$page->reference();
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
}
