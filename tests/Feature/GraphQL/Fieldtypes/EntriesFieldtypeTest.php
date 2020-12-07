<?php

namespace Tests\Feature\GraphQL\Fieldtypes;

use Facades\Statamic\Fields\BlueprintRepository;
use Facades\Tests\Factories\EntryFactory;
use Statamic\Facades\Blueprint;
use Tests\PreventSavingStacheItemsToDisk;
use Tests\TestCase;

/** @group graphql */
class EntriesFieldtypeTest extends TestCase
{
    use PreventSavingStacheItemsToDisk;

    /** @test */
    public function it_gets_multiple_entries()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_entries' => [2, 3],
        ])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => 'Related Post One'])->create();
        EntryFactory::collection('blog')->id('3')->data(['title' => 'Related Post Two'])->create();

        $article = Blueprint::makeFromFields([
            'related_entries' => ['type' => 'entries'],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_entries {
                id
                title
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
                    'related_entries' => [
                        ['id' => '2', 'title' => 'Related Post One'],
                        ['id' => '3', 'title' => 'Related Post Two'],
                    ],
                ],
            ]]);
    }

    /** @test */
    public function it_gets_single_entry()
    {
        EntryFactory::collection('blog')->id('1')->data([
            'title' => 'Main Post',
            'related_entry' => 2,
        ])->create();
        EntryFactory::collection('blog')->id('2')->data(['title' => 'Related Post One'])->create();

        $article = Blueprint::makeFromFields([
            'related_entry' => ['type' => 'entries', 'max_items' => 1],
        ]);

        BlueprintRepository::shouldReceive('in')->with('collections/blog')->andReturn(collect([
            'article' => $article->setHandle('article'),
        ]));

        $query = <<<'GQL'
{
    entry(id: "1") {
        title
        ... on Entry_Blog_Article {
            related_entry {
                id
                title
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
                    'related_entry' => [
                        'id' => '2',
                        'title' => 'Related Post One',
                    ],
                ],
            ]]);
    }
}
