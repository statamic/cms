<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class GridFieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    public function setUp(): void
    {
        parent::setUp();
        BlueprintRepository::partialMock();
    }

    /** @test */
    public function it_outputs_grid_fields()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'meals' => [
                ['food' => 'burger', 'drink' => 'coke'],
                ['food' => 'salad', 'drink' => 'water'],
            ],
        ])->create();

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

    /** @test */
    public function it_outputs_nested_grid_fields()
    {
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
}
