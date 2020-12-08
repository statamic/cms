<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class ReplicatorFieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

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
}
