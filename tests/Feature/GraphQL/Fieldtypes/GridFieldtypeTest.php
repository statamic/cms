<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class GridFieldtypeTest extends TestCase
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
    public function it_outputs_grid_fields()
    {
        $article = Blueprint::makeFromFields([
            'meals' => [
                'type' => 'grid',
                'fields' => [
                    ['handle' => 'food', 'field' => ['type' => 'text']],
                    ['handle' => 'drink', 'field' => ['type' => 'text']],
                ],
            ],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'meals' => [
                ['food' => 'burger', 'drink' => 'coke'],
                ['food' => 'salad', 'drink' => 'water'],
            ],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            meals {
                food
                drink
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
                    'meals' => [
                        ['food' => 'burger', 'drink' => 'coke'],
                        ['food' => 'salad', 'drink' => 'water'],
                    ],
                ],
            ]]);
    }

    /**
     * @test
     *
     * @see https://github.com/statamic/cms/issues/3200
     **/
    public function it_outputs_nested_grid_fields()
    {
        $article = Blueprint::makeFromFields([
            'meals' => [
                'type' => 'grid',
                'fields' => [
                    ['handle' => 'food', 'field' => ['type' => 'text']],
                    ['handle' => 'drink', 'field' => ['type' => 'text']],
                    [
                        'handle' => 'extras',
                        'field' => [
                            'type' => 'grid',
                            'fields' => [
                                ['handle' => 'item', 'field' => ['type' => 'text']],
                            ],
                        ],
                    ],
                ],
            ],
            // Add a top level field with the same handle as the nested grid
            // to ensure a conflict and that it doesn't accidentally pass.
            'extras' => [
                'type' => 'grid',
                'fields' => [
                    ['handle' => 'foo', 'field' => ['type' => 'text']],
                ],
            ],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'meals' => [
                [
                    'food' => 'burger',
                    'drink' => 'coke',
                    'extras' => [
                        ['item' => 'fries'],
                        ['item' => 'ketchup'],
                    ],
                ],
                [
                    'food' => 'salad',
                    'drink' => 'water',
                    'extras' => [
                        ['item' => 'dressing'],
                    ],
                ],
            ],
            'extras' => [
                ['foo' => 'bar'],
            ],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            meals {
                food
                drink
                extras {
                    item
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
                    'meals' => [
                        [
                            'food' => 'burger',
                            'drink' => 'coke',
                            'extras' => [
                                ['item' => 'fries'],
                                ['item' => 'ketchup'],
                            ],
                        ],
                        [
                            'food' => 'salad',
                            'drink' => 'water',
                            'extras' => [
                                ['item' => 'dressing'],
                            ],
                        ],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_outputs_grid_fields_with_value_based_subfields()
    {
        // Using an `entries` field set to max_items 1, which would augment
        // to a Value object. This test is checking that the Value object
        // is converted appropriately to an Entry. A similar thing would
        // happen for `assets` fields converting to Asset objects, etc.

        $article = Blueprint::makeFromFields([
            'things' => [
                'type' => 'grid',
                'fields' => [
                    [
                        'handle' => 'entry',
                        'field' => ['type' => 'entries', 'max_items' => 1],
                    ],
                ],
            ],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'things' => [
                ['entry' => '2'],
            ],
        ])->create();

        EntryFactory::collection('blog')->id('2')->data(['title' => 'Other Post'])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            things {
                entry {
                    title
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
                            'entry' => [
                                'title' => 'Other Post',
                            ],
                        ],
                    ],
                ],
            ]]);
    }
}
