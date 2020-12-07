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
}
