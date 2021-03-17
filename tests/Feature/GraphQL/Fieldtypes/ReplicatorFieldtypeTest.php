<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class ReplicatorFieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;
    use EnablesQueries;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    /** @test */
    public function it_outputs_replicator_fields()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'things' => [
                ['type' => 'meal', 'food' => 'burger', 'drink' => 'coke'],
                ['type' => 'car', 'make' => 'toyota', 'model' => 'corolla'],
                ['type' => 'meal', 'food' => 'salad', 'drink' => 'water'],
            ],
        ])->create();

        $article = Blueprint::makeFromFields([
            'things' => [
                'type' => 'replicator',
                'sets' => [
                    'meal' => [
                        'fields' => [
                            ['handle' => 'food', 'field' => ['type' => 'text']],
                            ['handle' => 'drink', 'field' => ['type' => 'text']],
                        ],
                    ],
                    'car' => [
                        'fields' => [
                            ['handle' => 'make', 'field' => ['type' => 'text']],
                            ['handle' => 'model', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            things {
                ... on Set_Things_Meal {
                    type
                    food
                    drink
                }
                ... on Set_Things_Car {
                    type
                    make
                    model
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
                'entry' => [
                    'title' => 'Main Post',
                    'things' => [
                        ['type' => 'meal', 'food' => 'burger', 'drink' => 'coke'],
                        ['type' => 'car', 'make' => 'toyota', 'model' => 'corolla'],
                        ['type' => 'meal', 'food' => 'salad', 'drink' => 'water'],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_outputs_nested_replicator_fields()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'things' => [
                ['type' => 'meal', 'food' => 'burger', 'drink' => 'coke', 'extras' => [
                    ['type' => 'food', 'item' => 'fries'],
                    ['type' => 'food', 'item' => 'ketchup'],
                ]],
                ['type' => 'car', 'make' => 'toyota', 'model' => 'corolla'],
                ['type' => 'meal', 'food' => 'salad', 'drink' => 'water', 'extras' => [
                    ['type' => 'food', 'item' => 'dressing'],
                ]],
            ],
        ])->create();

        $article = Blueprint::makeFromFields([
            'things' => [
                'type' => 'replicator',
                'sets' => [
                    'meal' => [
                        'fields' => [
                            ['handle' => 'food', 'field' => ['type' => 'text']],
                            ['handle' => 'drink', 'field' => ['type' => 'text']],
                            [
                                'handle' => 'extras',
                                'field' => [
                                    'type' => 'replicator',
                                    'sets' => [
                                        'food' => [
                                            'fields' => [
                                                ['handle' => 'item', 'field' => ['type' => 'text']],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'car' => [
                        'fields' => [
                            ['handle' => 'make', 'field' => ['type' => 'text']],
                            ['handle' => 'model', 'field' => ['type' => 'text']],
                        ],
                    ],
                ],
            ],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            things {
                ... on Set_Things_Meal {
                    type
                    food
                    drink
                    extras {
                        ... on Set_Things_Extras_Food {
                            type
                            item
                        }
                    }
                }
                ... on Set_Things_Car {
                    type
                    make
                    model
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
                'entry' => [
                    'title' => 'Main Post',
                    'things' => [
                        ['type' => 'meal', 'food' => 'burger', 'drink' => 'coke', 'extras' => [
                            ['type' => 'food', 'item' => 'fries'],
                            ['type' => 'food', 'item' => 'ketchup'],
                        ]],
                        ['type' => 'car', 'make' => 'toyota', 'model' => 'corolla'],
                        ['type' => 'meal', 'food' => 'salad', 'drink' => 'water', 'extras' => [
                            ['type' => 'food', 'item' => 'dressing'],
                        ]],
                    ],
                ],
            ]]);
    }

    /**
     * @test
     * @see https://github.com/statamic/cms/issues/3200
     **/
    public function it_outputs_replicator_fields_with_value_based_subfields()
    {
        // Using an `entries` field set to max_items 1, which would augment
        // to a Value object. This test is checking that the Value object
        // is converted appropriately to an Entry. A similar thing would
        // happen for `assets` fields converting to Asset objects, etc.

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'things' => [
                [
                    'type' => 'relation',
                    'entry' => '2',
                ],
            ],
        ])->create();

        EntryFactory::collection('blog')->id('2')->data(['title' => 'Other Post'])->create();

        $article = Blueprint::makeFromFields([
            'things' => [
                'type' => 'replicator',
                'sets' => [
                    'relation' => [
                        'fields' => [
                            [
                                'handle' => 'entry',
                                'field' => ['type' => 'entries', 'max_items' => 1],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            things {
                ... on Set_Things_Relation {
                    type
                    entry {
                        title
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
                'entry' => [
                    'title' => 'Main Post',
                    'things' => [
                        [
                            'type' => 'relation',
                            'entry' => [
                                'title' => 'Other Post',
                            ],
                        ],
                    ],
                ],
            ]]);
    }
}
