<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class BardFieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_outputs_bard_fields()
    {
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

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            things {
                ... on BardText_Things {
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

        $article = Blueprint::makeFromFields([
            'things' => ['type' => 'bard'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

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
}
