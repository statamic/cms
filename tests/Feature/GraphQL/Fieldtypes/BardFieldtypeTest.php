<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\Feature\GraphQL\EnablesQueries;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class BardFieldtypeTest extends TestCase
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
    public function it_outputs_bard_fields()
    {
        $article = Blueprint::makeFromFields([
            'things' => [
                'type' => 'bard',
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

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'things' => [
                ['type' => 'text', 'text' => 'first text'],
                ['type' => 'meal', 'food' => 'burger', 'drink' => 'coke'],
                ['type' => 'text', 'text' => 'second text'],
                ['type' => 'car', 'make' => 'toyota', 'model' => 'corolla'],
                ['type' => 'meal', 'food' => 'salad', 'drink' => 'water'],
                ['type' => 'text', 'text' => 'last text'],
            ],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            things {
                ... on BardText {
                    type
                    text
                }
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
                        ['type' => 'text', 'text' => '<p>first text</p>'],
                        ['type' => 'meal', 'food' => 'burger', 'drink' => 'coke'],
                        ['type' => 'text', 'text' => '<p>second text</p>'],
                        ['type' => 'car', 'make' => 'toyota', 'model' => 'corolla'],
                        ['type' => 'meal', 'food' => 'salad', 'drink' => 'water'],
                        ['type' => 'text', 'text' => '<p>last text</p>'],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_outputs_a_string_for_bard_fields_with_no_sets()
    {
        $article = Blueprint::makeFromFields([
            'things' => ['type' => 'bard'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'things' => [
                [
                    'type' => 'paragraph', 'content' => [
                        ['type' => 'text', 'text' => 'some text'],
                    ],
                ],
            ],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            things
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
                    'things' => '<p>some text</p>',
                ],
            ]]);
    }

    /** @test */
    public function it_outputs_nested_bard_fields()
    {
        $article = Blueprint::makeFromFields([
            'things' => [
                'type' => 'bard',
                'sets' => [
                    'meal' => [
                        'fields' => [
                            ['handle' => 'food', 'field' => ['type' => 'text']],
                            ['handle' => 'drink', 'field' => ['type' => 'text']],
                            [
                                'handle' => 'extras',
                                'field' => [
                                    'type' => 'bard',
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

        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'things' => [
                ['type' => 'text', 'text' => 'first text'],
                ['type' => 'meal', 'food' => 'burger', 'drink' => 'coke', 'extras' => [
                    ['type' => 'food', 'item' => 'fries'],
                    ['type' => 'food', 'item' => 'ketchup'],
                ]],
                ['type' => 'text', 'text' => 'second text'],
                ['type' => 'car', 'make' => 'toyota', 'model' => 'corolla'],
                ['type' => 'meal', 'food' => 'salad', 'drink' => 'water', 'extras' => [
                    ['type' => 'food', 'item' => 'dressing'],
                ]],
                ['type' => 'text', 'text' => 'last text'],
            ],
        ])->create();

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            things {
                ... on BardText {
                    type
                    text
                }
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
                        ['type' => 'text', 'text' => '<p>first text</p>'],
                        ['type' => 'meal', 'food' => 'burger', 'drink' => 'coke', 'extras' => [
                            ['type' => 'food', 'item' => 'fries'],
                            ['type' => 'food', 'item' => 'ketchup'],
                        ]],
                        ['type' => 'text', 'text' => '<p>second text</p>'],
                        ['type' => 'car', 'make' => 'toyota', 'model' => 'corolla'],
                        ['type' => 'meal', 'food' => 'salad', 'drink' => 'water', 'extras' => [
                            ['type' => 'food', 'item' => 'dressing'],
                        ]],
                        ['type' => 'text', 'text' => '<p>last text</p>'],
                    ],
                ],
            ]]);
    }

    /**
     * @test
     * @see https://github.com/statamic/cms/issues/3200
     **/
    public function it_outputs_bard_fields_with_value_based_subfields()
    {
        // Using an `entries` field set to max_items 1, which would augment
        // to a Value object. This test is checking that the Value object
        // is converted appropriately to an Entry. A similar thing would
        // happen for `assets` fields converting to Asset objects, etc.

        $article = Blueprint::makeFromFields([
            'things' => [
                'type' => 'bard',
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
