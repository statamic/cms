<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class ToggleFieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_a_boolean()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'yup' => true,
            'nope' => false,
        ])->create();

        $article = Blueprint::makeFromFields([
            'yup' => ['type' => 'toggle'],
            'nope' => ['type' => 'toggle'],
            'undefined' => ['type' => 'toggle'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        ... on Entry_Blog_Article {
            yup
            nope
            undefined
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
                    'yup' => true,
                    'nope' => false,
                    'undefined' => false,
                ],
            ]]);
    }
}
