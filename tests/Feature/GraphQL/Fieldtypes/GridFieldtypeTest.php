<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

#[Group('graphql')]
class GridFieldtypeTest extends TestCase
{
    use EnablesQueries;
    use PreventSavingStacheItemsToDisk;

    protected $enabledQueries = ['collections'];

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    #[Test]
    public function it_outputs_grid_fields()
    {
        $article = Blueprint::makeFromFields([
            'meals' => [
                'type' => 'grid',
                'fields' => [
                    ['handle' => 'food', 'field' => ['type' => 'text']],
                    ['handle' => 'drink', 'field' => ['type' => 'markdown']], // using markdown to show nested fields are resolved using their fieldtype.
                    ['handle' => 'stuff', 'field' => ['type' => 'entries']], // using entries to query builders get resolved
                ],
            ],
        ]);

        $stuff = Blueprint::makeFromFields([]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        BlueprintRepository::shouldReceive('in')->with('collections/stuff')->andReturn(collect([
            'stuff' => $stuff->setHandle('stuff'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'meals' => [
                ['id' => '1', 'food' => 'burger', 'drink' => 'coke _zero_'],
                ['food' => 'salad', 'drink' => 'water', 'stuff' => ['stuff1']], // id intentionally omitted
            ],
        ])->create();

        EntryFactory::collection('stuff')->id('stuff1')->data(['title' => 'One'])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            meals {
                id
                food
                drink
                drink_md: drink(format: "markdown")
                stuff {
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
                    'meals' => [
                        ['id' => '1', 'food' => 'burger', 'drink' => "<p>coke <em>zero</em></p>\n", 'drink_md' => 'coke _zero_', 'stuff' => []],
                        ['id' => null, 'food' => 'salad', 'drink' => "<p>water</p>\n", 'drink_md' => 'water', 'stuff' => [['title' => 'One']]],
                    ],
                ],
            ]]);
    }

    /**
     * @see https://github.com/statamic/cms/issues/3200
     **/
    #[Test]
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

    #[Test]
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
